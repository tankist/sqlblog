<?php
abstract class Zend_Db_Routine_Abstract {
	
	/**
	 * Default Zend_Db_Adapter_Abstract object.
	 *
	 * @var Zend_Db_Adapter_Abstract
	 */
	protected static $_defaultDb;
	
	/**
	 * Zend_Db_Adapter_Abstract object.
	 *
	 * @var Zend_Db_Adapter_Abstract
	 */
	protected $_db;
	
	/**
	 * Sets the default Zend_Db_Adapter_Abstract for all Zend_Db_Table objects.
	 *
	 * @param  mixed $db Either an Adapter object, or a string naming a Registry key
	 * @return void
	 */
	public static function setDefaultAdapter($db = null)
	{
		self::$_defaultDb = self::_setupAdapter($db);
	}

	/**
	 * Gets the default Zend_Db_Adapter_Abstract for all Zend_Db_Table objects.
	 *
	 * @return Zend_Db_Adapter_Abstract or null
	 */
	public static function getDefaultAdapter()
	{
		return self::$_defaultDb;
	}
	
	public function __construct()
	{
		/**
		 * Allow a scalar argument to be the Adapter object or Registry key.
		 */
		$this->_setup();
	}
	
	/**
	 * Turnkey for initialization of a table object.
	 * Calls other protected methods for individual tasks, to make it easier
	 * for a subclass to override part of the setup logic.
	 *
	 * @return void
	 */
	 protected function _setup()
	{
		$this->_setupDatabaseAdapter();
	}

	/**
	 * Initialize database adapter.
	 *
	 * @return void
	 */
	protected function _setupDatabaseAdapter()
	{
		if (! $this->_db) {
			$this->_db = self::getDefaultAdapter();
			if (!$this->_db instanceof Zend_Db_Adapter_Abstract) {
				require_once 'Zend/Db/Routine/Exception.php';
				throw new Zend_Db_Routine_Exception('No adapter found for ' . get_class($this));
			}
		}
	}
	
	/**
	 * @param  mixed $db Either an Adapter object, or a string naming a Registry key
	 * @return Zend_Db_Table_Abstract Provides a fluent interface
	 */
	protected function _setAdapter($db)
	{
		$this->_db = self::_setupAdapter($db);
		return $this;
	}

	/**
	 * Gets the Zend_Db_Adapter_Abstract for this particular Zend_Db_Table object.
	 *
	 * @return Zend_Db_Adapter_Abstract
	 */
	public function getAdapter()
	{
		return $this->_db;
	}

	/**
	 * @param  mixed $db Either an Adapter object, or a string naming a Registry key
	 * @return Zend_Db_Adapter_Abstract
	 * @throws Zend_Db_Table_Exception
	 */
	protected static function _setupAdapter($db)
	{
		if ($db === null) {
			return null;
		}
		if (is_string($db)) {
			require_once 'Zend/Registry.php';
			$db = Zend_Registry::get($db);
		}
		if (!$db instanceof Zend_Db_Adapter_Abstract) {
			require_once 'Zend/Db/Routine/Exception.php';
			throw new Zend_Db_Routine_Exception('Argument must be of type Zend_Db_Adapter_Abstract, or a Registry key where a Zend_Db_Adapter_Abstract object is stored');
		}
		return $db;
	}
	
	abstract public function describe($name);
}
?>