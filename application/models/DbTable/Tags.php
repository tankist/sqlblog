<?php

class Application_Model_DbTable_Tags extends Zend_Db_Table_Abstract
{

    protected $_name = 'sb_tags';

	protected $_dependentTables = array('Application_Model_DbTable_PostTags');

}

