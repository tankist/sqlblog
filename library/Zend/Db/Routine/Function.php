<?php
class Zend_Db_Routine_Function extends Zend_Db_Routine_Abstract {
	public function describe($name) {
		$create = $this->getAdapter()->fetchRow('SHOW CREATE FUNCTION ' . $this->_db->quoteIdentifier($name));
		$body = (array_key_exists('Create Function', $create))?$create['Create Function']:'';
		$body = preg_replace('$\s+$im', ' ', $body);
		if (preg_match_all('$CREATE.+?FUNCTION[^\(]+\((.*)\)[^\(]+BEGIN$i', $body, $matches)) {
			$paramsList = explode(',', $matches[1][0]);
			$paramsList = array_map('trim', $paramsList);
			$params = array();
			foreach ($paramsList as $param) {
				list($paramName, $dataType) = explode(' ', $param);
				$param = array(
					'name' => $paramName,
					'dataType' => $dataType
				);
				if ($pos = strpos($dataType, '(')) {
					list($dataType, $length) = explode('(', $dataType);
					$length = trim($length, '()');
					$param['dataType'] = $dataType;
					$param['length'] = $length;
				}
				$params[] = $param;
			}
			return array(
				'params' => $params,
				'name' => $name
			);
		}
		return false;
	}
	
	public function __call($name, $params) {
		$result = array();
		$routineDefinition = $this->describe($name);
		if (!$routineDefinition) {
			throw new Zend_Db_Routine_Exception('Function ' . $name . ' cannot be found');
		}
		$adapter = $this->getAdapter();
		$paramsDefinition = $routineDefinition['params'];
		$inputParams = array();
		for($i = 0;$i < count($paramsDefinition);$i++){
			$value = ($i < count($params))?$params[$i]:null;
			$inputParams[] = $adapter->quote($value);
		}
		$result = $adapter->fetchOne('SELECT ' . $adapter->quoteIdentifier($name) . '(' . join(', ', $inputParams) . ')');
		return $result;
	}
}
?>
