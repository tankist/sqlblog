<?php

class Application_Form_Comment extends Zend_Form
{

	public function init()
	{
		$this
			->addElement('hidden', 'post_id')
			->addElement('text', 'user', array('label' => 'Name:', 'required' => true))
			->addElement('textarea', 'text', array('label' => 'Comment:', 'required' => true))
			->addElement('submit', 'submit', array('label' => 'Add comment'));
	}

	public function prepareDecorators()
	{
		$this->setDecorators(array(
			'FormElements',
			'FormErrors',
			'Form'
		));
		
		return $this;
	}


}