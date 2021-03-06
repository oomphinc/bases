<?php

/**
 * A base entity for an object that can be instantiated.
 */

namespace OomphInc\Bases;

class InstantiableEntity extends BaseEntity {

	protected $name;

	function __construct( $name = null, array $properties = [] ) {
		if ( isset( $name ) ) {
			$this->name = $name;
		}
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
