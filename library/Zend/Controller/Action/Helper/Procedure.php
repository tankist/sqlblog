<?php
class Zend_Controller_Action_Helper_Procedure extends Zend_Controller_Action_Helper_Abstract {
	
	public function direct() {
		$routine = new Zend_Db_Routine_Procedure();
		return $routine;
	}
	
}
?>
