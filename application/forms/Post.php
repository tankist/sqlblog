<?php

class Application_Form_Post extends Zend_Form
{

	public function init()
	{
		$this
			->addElement('hidden', 'id')
			->addElement('text', 'title', array('label' => 'Title:', 'required' => true))
			->addElement('textarea', 'text', array('label' => 'Text:', 'required' => true))
			->addElement('text', 'tags', array('label' => 'Tags:', 'required' => true))
			->addElement('submit', 'submit', array('label' => 'Add Post'));
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