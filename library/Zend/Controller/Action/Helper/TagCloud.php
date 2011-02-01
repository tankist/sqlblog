<?php
class Zend_Controller_Action_Helper_TagCloud extends Zend_Controller_Action_Helper_Abstract {
	
	public function direct() {
		$tags = new Application_Model_DbTable_Tags();
		$tagsRecordset = $tags->fetchAll(null, 'tag');
		$cloudTags = array();
		foreach ($tagsRecordset as $tag) {
			$cloudTags[] = array(
				'title' => $tag->tag,
				'weight' => $tag->weight,
				'params' => array(
					'url' => '/tags/index/tag/' . $this->getActionController()->view->escape($tag->tag)
				)
			);
		}
		$cloud = new Zend_Tag_Cloud(array(
			'tags' => $cloudTags
		));
		return $cloud;
	}
	
}
?>
