<?php

class TagsController extends Zend_Controller_Action
{

	public function init()
	{
		Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');
		$this->view->tagCloud = $this->_helper->tagCloud();
	}

	public function indexAction()
	{
		$page = $this->_getParam('p', 1);
		$tag = $this->_getParam('tag');
		if (!$tag) {
			$this->_redirect('/posts');
		}
		$tag_id = $this->_helper->function()->Tag_getIdByTag($tag);
		$postsTable = new Application_Model_DbTable_Posts();
		$postTagsTable = new Application_Model_DbTable_PostTags();
		$paginator = Zend_Paginator::factory(
			$postsTable->select(true)->setIntegrityCheck(false)
				->joinInner(
					array('pt' => $postTagsTable->info(Application_Model_DbTable_PostTags::NAME)), 
					'pt.post_id = id'
				)
				->where('pt.tag_id = ?', $tag_id),
				'Posts'
		);
		$paginator->setItemCountPerPage(10)->setCurrentPageNumber($page);
		$this->view->postsPaginator = $paginator;
	}


}

