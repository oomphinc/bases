<?php

/**
 * A base entity for an object that can be instantiated.
 */

namespace OomphInc\Bases;

class InstantiableEntity extends BaseEntity {

	function __construct( $name, array $properties = [] ) {
		$this->name = $name;
		// for convenience, properties can be passed as an array upon instantiation
		foreach ( $properties as $name => $value ) {
			$this->$name = $value;
		}
		parent::__construct();
	}

	function get_name() {
		return $this->name;
	}

}
