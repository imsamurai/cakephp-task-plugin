<?php

/**
 * Author: imsamurai <im.samuray@gmail.com>
 * Date: 15.10.2012
 * Time: 12:39:16
 *
 */

/**
 * Main class for types
 * 
 * @package Type
 * @package Task
 */
class Type {

	/**
	 * Current type
	 *
	 * @var string $__type
	 */
	private $__type;

	/**
	 * Constructor
	 * 
	 * @param string $type Type
	 * @throws Exception On invalid type
	 */
	public function __construct($type) {
		if (static::isValidType($type)) {
			$this->__type = $type;
		} else {
			$exception = get_class($this) . 'InvalidTypeException';
			throw new $exception($type);
		}
	}

	/**
	 * Check if $type exists in class constants
	 *
	 * @param string $type
	 *
	 * @return bool
	 */
	public static function isValidType($type) {
		return in_array($type, static::getTypes(), true);
	}

	/**
	 * Creates new type object from string type.
	 * If type not valid - creates default type object
	 *
	 * @param string $type
	 *
	 * @return Type
	 */
	public static function createFromString($type) {
		if (!static::isValidType($type)) {
			$type = static::_DEFAULT;
		}

		$class = get_called_class();

		return new $class($type);
	}

	/**
	 * Get all types as array
	 *
	 * @return array
	 */
	public static function getTypes() {
		$class = new ReflectionClass(get_called_class());
		$types = $class->getConstants();
		unset($types['_DEFAULT']);
		return $types;
	}

	/**
	 * Return string type
	 *
	 * @return string
	 */
	public function toString() {
		return $this->__type;
	}

	/**
	 * Return string type
	 *
	 * @return string
	 */
	public function __toString() {
		return $this->toString();
	}

}
