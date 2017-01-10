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
			'core.display_forums_modify_template_vars'	=> 'replace_last_post_subject',
		);
	}

	/** @var \phpbb\user */
	protected $user;

	/** @var string */
	protected $topics_table;

	/**
	* Constructor
	*
	* @param \phpbb\user	$user			User object
	* @param string			$topics_table	Topics table
	*/
	public function __construct(\phpbb\user $user, $topics_table)
	{
		$this->user = $user;
		$this->topics_table = $topics_table;
	}

	public function fetch_first_posts_subject_of_last_topic($event)
	{
		$sql_ary = $event['sql_ary'];

		$sql_ary['SELECT'] .= ', senky_removesubjectfromreplies_t.topic_title';
		$sql_ary['LEFT_JOIN'][] = array(
			'FROM'	=> array(
				$this->topics_table => 'senky_removesubjectfromreplies_t',
			),
			'ON'	=> 'senky_removesubjectfromreplies_t.topic_last_post_id = f.forum_last_post_id',
		);

		$event['sql_ary'] = $sql_ary;
	}

	public function replace_last_post_subject($event)
	{
		$forum_row = $event['forum_row'];

		$last_post_subject = censor_text($event['row']['topic_title']);
		$last_post_subject_truncated = truncate_string($last_post_subject, 30, 255, false, $this->user->lang['ELLIPSIS']);

		$forum_row['LAST_POST_SUBJECT'] = $last_post_subject;
		$forum_row['LAST_POST_SUBJECT_TRUNCATED'] = $last_post_subject_truncated;

		$event['forum_row'] = $forum_row;
	}
}
