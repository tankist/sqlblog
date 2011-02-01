<?php
class Zend_Controller_Action_Helper_Function extends Zend_Controller_Action_Helper_Abstract {
	
	public function direct() {
		$routine = new Zend_Db_Routine_Function();
		return $routine;
	}
	
}
?>
