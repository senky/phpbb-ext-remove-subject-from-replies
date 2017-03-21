<?php
/**
*
* Auto Groups extension for the phpBB Forum Software package.
*
* @copyright (c) 2014 phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace senky\removesubjectfromreplies\migrations\v20x;

class m1_add_topic_last_post_id_index extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v31x\v317rc1');
	}

	public function update_schema()
	{
		return array(
			'add_index'	=> array(
				$this->table_prefix . 'topics'	=> array(
					'rsfr_tlpi' => array('topic_last_post_id'),
			    ),
            ),
		);
	}

	public function revert_schema()
	{
		return array(
			'drop_keys'	=> array(
				$this->table_prefix . 'topics'	=> array(
					'rsfr_tlpi',
			    ),
			),
		);
	}
}