<?php

class PostsController extends Zend_Controller_Action
{

	public function init()
	{
		Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');
		$this->view->tagCloud = $this->_helper->tagCloud();
	}

	public function indexAction()
	{
		$page = $this->_getParam('p', 1);
		$postsTable = new Application_Model_DbTable_Posts();
		$paginator = Zend_Paginator::factory($postsTable->select());
		$paginator->setItemCountPerPage(10)->setCurrentPageNumber($page);
		$this->view->postsPaginator = $paginator;
	}

	public function addAction()
	{
		$this->view->form = $form = new Application_Form_Post(array(
			'name' => 'postForm',
			'action' => '/posts/save/'
		));
		$form->prepareDecorators();
	}

	public function editAction()
	{
		$post_id = $this->_getParam('id');
		
		
		
		$this->view->form = $form = new Application_Form_Post(array(
			'name' => 'postForm',
			'action' => '/posts/save/'
		));
		
		$result = $this->_helper->procedure()->Posts_getById($post_id);
		if (!empty($result['rows'])) {
			$postData = $result['rows'][$post_id];
			$tagsRowset = $this->_helper->procedure()->Post_getTags($post_id);
			if (!empty($tagsRowset)) {
				$tags = array();
				foreach ($tagsRowset['rows'] as $_t) {
					$tags[] = $_t['tag'];
				}
				$postData['tags'] = join(', ', $tags);
			}
			$form->populate($postData);
		}
		else {
			$this->_redirect('/posts');
		}
		
		$form->prepareDecorators();
	}

	public function saveAction()
	{
		$post_id = $this->_getParam('id');
		$this->view->form = $form = new Application_Form_Post(array(
			'name' => 'postForm',
			'action' => '/posts/save/'
		));
		if ($this->getRequest()->isPost()) {
			if ($form->isValid($this->getRequest()->getPost())) {
				$result = $this->_helper->procedure()->Posts_save(
					$form->getValue('title'),
					$form->getValue('text'),
					$post_id
				);
				if ($post_id) {
					$tags = $form->getValue('tags');
					$tags = explode(',', $tags);
					$tags = array_map('trim', $tags);
					$this->_helper->procedure()->Post_clearTags($post_id);
					foreach ($tags as $tag) {
						$this->_helper->procedure()->Post_addTag($post_id, $tag);
					}
					$this->_redirect('/posts/view/id/' . $post_id);
				}
			}
			$this->view->form = $form->prepareDecorators();
		}
		else {
			$this->_redirect('/posts');
		}
	}

	public function deleteAction()
	{
		$post_id = $this->_getParam('id');
		if ($post_id) {
			$this->_helper->procedure()->Post_remove($post_id);
		}
		$this->_redirect('/posts');
	}

	public function addCommentAction()
	{
		$post_id = $this->_getParam('post_id');
		if (!$post_id) {
			$this->_redirect('/posts');
		}
		$commentForm = new Application_Form_Comment(array(
			'name' => 'commentForm',
			'action' => '/posts/add-comment/'
		));
		if ($this->getRequest()->isPost()) {
			if ($commentForm->isValid($this->getRequest()->getPost())) {
				$result = $this->_helper->procedure()->Post_addComment(
					$post_id, 
					$commentForm->getValue('user'),
					$commentForm->getValue('text')
				);
			}
		}
		$this->_redirect('/posts/view/id/' . $post_id);
	}

	public function viewAction()
	{
		$post_id = $this->_getParam('id');
		if (!$post_id) {
			$this->_redirect('/posts');
		}
		$result = $this->_helper->procedure()->Posts_getById($post_id);
		if (!$result) {
			$this->_redirect('/posts');
		}
		$this->view->post = $result['rows'][$post_id];
		$tags = $this->_helper->procedure()->Post_getTags($post_id);
		$this->view->tags = ($tags)?$tags['rows']:array();
		
		$commentsResultset = $this->_helper->procedure()->Post_getComments($post_id);
		$this->view->comments = (!empty($commentsResultset))?$commentsResultset['rows']:array();
		
		$commentForm = new Application_Form_Comment(array(
			'name' => 'commentForm',
			'action' => '/posts/add-comment/'
		));
		$commentForm->getElement('post_id')->setValue($post_id);
		$this->view->commentForm = $commentForm->prepareDecorators();
	}


}