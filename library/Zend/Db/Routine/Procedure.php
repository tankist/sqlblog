<?php
class Zend_Db_Routine_Procedure extends Zend_Db_Routine_Abstract {
	
	public function describe($name) {
		$create = $this->getAdapter()->fetchRow('SHOW CREATE PROCEDURE ' . $this->_db->quoteIdentifier($name));
		$body = (array_key_exists('Create Procedure', $create))?$create['Create Procedure']:'';
		$body = preg_replace('$\s+$im', ' ', $body);
		if (preg_match_all('$CREATE.+?PROCEDURE[^\(]+\((.*)\)[^\(]+BEGIN$i', $body, $matches)) {
			$paramsList = explode(',', $matches[1][0]);
			$paramsList = array_map('trim', $paramsList);
			$params = array();
			foreach ($paramsList as $param) {
				list($type, $paramName, $dataType) = explode(' ', $param);
				$param = array(
					'type' => $type,
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
			throw new Zend_Db_Routine_Exception('Procedure ' . $name . ' cannot be found');
		}
		$adapter = $this->getAdapter();
		$paramsDefinition = $routineDefinition['params'];
		$outputParams = $inputParams = $predefinedParams = array();
		for($i = 0;$i < count($paramsDefinition);$i++){
			$value = ($i < count($params))?$params[$i]:null;
			$paramDefinition = $paramsDefinition[$i];
			switch ($paramDefinition['type']) {
				case 'IN':
					$inputParams[] = $adapter->quote($value);
					break;
				case 'INOUT':
					$predefinedParams[] = 'SET @' . $adapter->quoteIdentifier($paramDefinition['name']) . '=' . $adapter->quote($value);
				case 'OUT':
					$paramName = $adapter->quoteIdentifier($paramDefinition['name']);
					$inputParams[] = '@' . $paramName;
					$outputParams[] = '@' . $paramName . ' AS ' . $paramName;
					break;
			}
		}
		try {
			$adapter->beginTransaction();
			if (!empty($predefinedParams)) {
				$adapter->query(join(', ', $predefinedParams));
			}
			$result['rows'] = $adapter->fetchAssoc('CALL ' . $adapter->quoteIdentifier($name) . '(' . join(', ', $inputParams) . ')');
			if (!empty($outputParams)) {
				$result['outputParams'] = $adapter->fetchRow('SELECT ' . join(', ', $outputParams));
			}
			$adapter->commit();
		}
		catch (Zend_Db_Exception $e) {
			$adapter->rollBack();
			throw $e;
		}
		return $result;
	}
	
}
?>
