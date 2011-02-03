<?php

class Application_Model_DbTable_Posts extends Zend_Db_Table_Abstract
{

    protected $_name = 'sb_posts';
	
	protected $_rowClass = 'Application_Model_Row_Post';

	protected $_dependentTables = array('Application_Model_DbTable_PostTags');

}

