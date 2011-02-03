<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Posts
 *
 * @author victor
 */
class Zend_Paginator_Adapter_Posts extends Zend_Paginator_Adapter_DbTableSelect {

	public function getItems($offset, $itemCountPerPage) {
		$posts = parent::getItems($offset, $itemCountPerPage);
		foreach ($posts as $post) {
			$post->retrieveTags();
		}
		return $posts;
	}

}