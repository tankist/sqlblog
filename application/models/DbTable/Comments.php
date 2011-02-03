<?php

class Application_Model_DbTable_Comments extends Zend_Db_Table_Abstract
{

    protected $_name = 'sb_comments';

	protected $_referenceMap = array(
		'Post' => array(
			'columns' => 'post_id',
			'refTableClass' => 'Application_Model_DbTable_Posts',
			'refColumn' => 'id'
		)
	);

}

