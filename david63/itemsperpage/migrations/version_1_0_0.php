<?php
/**
*
* @package Items per Page Extension
* @copyright (c) 2014 david63
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace david63\itemsperpage\migrations;

class version_1_0_0 extends \phpbb\db\migration\migration
{
	public function update_data()
	{
		return array(
			array('config.add', array('itemsperpage_override', 0)),
			array('config.add', array('itemsperpage_version', '1.0.0')),
		);
	}

	public function update_schema()
	{
		return array(
			'add_columns'	=> array(
				$this->table_prefix . 'users'	=> array(
					'user_posts_per_page'	=> array('TINT:2', 0),
					'user_topics_per_page'	=> array('TINT:2', 0),
				),
			),
		);
	}

	/**
	* Drop the columns schema from the tables
	*
	* @return array Array of table schema
	* @access public
	*/
	public function revert_schema()
	{
		return array(
			'drop_columns'	=> array(
				$this->table_prefix . 'users'	=> array(
					'user_posts_per_page',
					'user_topics_per_page',
				),
			),
		);
	}
}
