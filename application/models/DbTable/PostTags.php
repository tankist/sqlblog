<?php

class Application_Model_DbTable_PostTags extends Zend_Db_Table_Abstract
{

    protected $_name = 'sb_post_tags';

	protected $_referenceMap = array(
		'Post' => array(
			'columns' => 'post_id',
			'refTableClass' => 'Application_Model_DbTable_Posts',
			'refColumn' => 'id'
		),
		'Tag' => array(
			'columns' => 'tag_id',
			'refTableClass' => 'Application_Model_DbTable_Tags',
			'refColumn' => 'id'
		)
	);

}

