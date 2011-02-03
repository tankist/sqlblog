<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Post
 *
 * @author victor
 */
class Application_Model_Row_Post extends Zend_Db_Table_Row {

	protected $_tags = null;

	public function getTags() {
		return $this->_tags;
	}

	public function setTags($_tags) {
		$this->_tags = $_tags;
		return $this;
	}
	
	public function retrieveTags() {
		$this->_tags = $this->findManyToManyRowset('Application_Model_DbTable_Tags', 'Application_Model_DbTable_PostTags');
	}

}