<?php

/**
 * Entity base class
 */

namespace OomphInc\Bases;

abstract class BaseEntity {

	// Instances of derivative classes
	private static $instances = [];
	// Entity name (e.g. post_type or tax slug)
	const name = '';
	// Actions and filters
	protected static $shared_hooks = [];
	// custom hooks for the child class
	protected $hooks = [
		// [ 'hook', 'method', priority, num_args ],
	];

	/**
	 * Get a single canonical instance of a derivative class.
	 * @return object the instantiated class
	 */
	final static function instance() {
		$class = get_called_class();

		if ( !isset( self::$instances[ $class ] ) ) {
			self::$instances[ $class ] = new $class();
		}

		return self::$instances[ $class ];
	}

	private function __clone() {}

	/**
	 * Return all of the CPT instances
	 *
	 * @todo: this is all entity instances - how to get only for the specific base type?
	 */
	final static function get_instances() {
		return self::$instances;
	}

	/**
	 * Register hooks
	 */
	protected function __construct() {
		// add the hooks! add_action is a wrapper for add_filter
		foreach ( array_merge( $this->hooks, static::$shared_hooks ) as $hook ) {
			// argument defaults - using 15 for accepted args, only those present get passed anyway
			$args = array_replace( [ null, null, 10, 15 ], $hook );
			// shorthand for methods named after the hook
			if ( !isset( $args[1] ) ) {
				$args[1] = $args[0];
			}
			// change the method name to a valid callable
			$args[1] = [ $this, $args[1] ];
			call_user_func_array( 'add_filter', $args );
		}

		// initialize the the traits
		foreach ( class_uses( $this ) as $trait ) {
			// break up trait into namespace parts
			$trait = explode( '\\', $trait );
			// get the init method name
			$init = '_init_' . end( $trait );
			if ( is_callable( [ $this, $init ] ) ) {
				$this->$init();
			}
		}

		$this->init();
	}

	/**
	 * Get the post type this class is representing.
	 *
	 * @return string The post type slug, found in the 'post_type' class constant.
	 */
	function get_name() {
		return static::name;
	}

	/**
	 * A place for the derivative class to implement additional hooks.
	 */
	function init() {}

}
