<?php
class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
	protected function _initDbRoutine() {
		$this->bootstrap('db');
		Zend_Db_Routine_Abstract::setDefaultAdapter($this->getResource('db'));
	}
}

