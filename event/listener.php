<?php
/**
*
* Remove Subject from Replies extension for the phpBB Forum Software package.
*
* @copyright (c) 2016 Jakub Senko <jakubsenko@gmail.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace senky\removesubjectfromreplies\event;

/**
* @ignore
*/
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
* Event listener
*/
class listener implements EventSubscriberInterface
{
	static public function getSubscribedEvents()
	{
		return array(
			'core.display_forums_modify_sql'			=> 'fetch_first_posts_subject_of_last_topic',
			'core.display_forums_modify_forum_rows'		=> 'update_subarray_data',
			'core.display_forums_modify_template_vars'	=> 'assign_reply_flag',
			'core.posting_modify_template_vars'			=> 'remove_preview_subject',
		);
	}

	/** @var \phpbb\user */
	protected $user;

	/** @var string */
	protected $topics_table;

	/**
	* Constructor
	*
	* @param \phpbb\user				$user			User object
	* @param string						$topics_table	Topics table
	*/
	public function __construct(\phpbb\user $user, $topics_table)
	{
		$this->user = $user;
		$this->topics_table = $topics_table;
	}

	public function fetch_first_posts_subject_of_last_topic($event)
	{
		$sql_ary = $event['sql_ary'];

		// We need to select forum_last_post_time once more because it is overwritten
		// before core.display_forums_modify_forum_rows thus we are unable to identify
		// newer posts in subforums.
		$sql_ary['SELECT'] .= ', senky_removesubjectfromreplies_t.topic_first_post_id, senky_removesubjectfromreplies_t.topic_title as forum_last_post_subject, f.forum_last_post_time as srsfr_last_post_time';
		$sql_ary['LEFT_JOIN'][] = array(
			'FROM'	=> array(
				$this->topics_table => 'senky_removesubjectfromreplies_t',
			),
			'ON'	=> 'senky_removesubjectfromreplies_t.topic_last_post_id = f.forum_last_post_id',
		);

		$event['sql_ary'] = $sql_ary;
	}

	public function update_subarray_data($event)
	{
		if ($event['row']['forum_type'] != FORUM_CAT && $event['row']['srsfr_last_post_time'] > $event['forum_rows'][$event['parent_id']]['srsfr_last_post_time'])
		{
			$forum_rows = $event['forum_rows'];
			$forum_rows[$event['parent_id']]['topic_first_post_id'] = $event['row']['topic_first_post_id'];
			$event['forum_rows'] = $forum_rows;
		}
	}

	public function assign_reply_flag($event)
	{
		$forum_row = $event['forum_row'];
		$forum_row['SENKY_REMOVESUBJECTFROMREPLIES_RE'] = $event['row']['topic_first_post_id'] != $event['row']['forum_last_post_id'];
		$event['forum_row'] = $forum_row;
	}

	public function remove_preview_subject($event)
	{
		if (!($event['mode'] == 'post' || ($event['mode'] == 'edit' && $event['post_data']['topic_first_post_id'] == $event['post_data']['post_id'])))
		{
			$page_data = $event['page_data'];
			$page_data['PREVIEW_SUBJECT'] = '';
			$event['page_data'] = $page_data;
		}
	}
}
