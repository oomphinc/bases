<?php

/**
 * Custom Post Type base class
 */

namespace OomphInc\Bases\Post;

abstract class CustomPostType {

	// Instances of derivative classes
	private static $instance = [];
	// The post type slug
	const post_type = '';
	// Any additional arguments to register_post_type
	protected $post_type_args = [];
	// Taxonomies that this post type can be assigned terms from
	protected $taxonomies = [];
	// Leaving $label_singular empty implies the CPT is registered by other means
	protected $label_singular;
	protected $label_plural;

	/**
	 * Get a single canonical instance of a derivative class.
	 * @return CustomPostType the instantiated class
	 */
	final static function instance() {
		$class = get_called_class();

		if( !isset( self::$instances[ $class ] ) ) {
			self::$instances[ $class ] = new $class();
		}

		return self::$instances[ $class ];
	}

	/**
	 * Return all of the CPT instances
	 */
	final static function get_types() {
		return self::$instances;
	}

	private function __clone() {}

	/**
	 * Register hooks
	 */
	protected function __construct() {
		add_action( 'init', [ $this, 'register_cpt' ], 0 );
		add_action( 'save_post', [ $this, 'save_post' ] );
		add_action( 'add_meta_boxes_' . $this->get_post_type(), [ $this, 'register_meta_boxes' ] );

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
	function get_post_type() {
		return static::post_type;
	}

	/**
	 * Register CPT, but only if $this->label_singular is set.
	 *
	 * @action init
	 */
	function register_cpt() {
		if ( empty( $this->label_singular ) ) {
			return;
		}
		else if ( empty( $this->label_plural ) ) {
			$this->label_plural = $this->label_singular . 's';
		}

		$args = wp_parse_args( $this->post_type_args, [
			'labels' => [
				'name' => $this->label_plural,
				'all_items' => 'All ' . $this->label_plural,
				'singular_name' => $this->label_singular,
				'add_new_item' => 'Add New ' . $this->label_singular,
				'edit_item' => 'Edit ' . $this->label_singular,
				'new_item' => 'New ' . $this->label_singular,
				'view_item' => 'View ' . $this->label_singular,
				'search_items' => 'Search ' . $this->label_plural,
				'not_found' => 'No ' . strtolower( $this->label_plural ) . ' found',
			],
			'show_ui' => true,
			'public' => true,
			'has_archive' => true,
			'show_in_nav_menus' => true,
			'menu_position' => 20,
			'map_meta_cap' => true,
			'supports' => [
				'title',
				'editor',
				'thumbnail',
			],
			'hierarchical' => false,
			'rewrite' => [ 'slug' => $this->get_post_type(), 'with_front' => false, 'feeds' => true ],
			'capability_type' => $this->get_post_type(),
			'taxonomies' => [],
		] );

		register_post_type( $this->get_post_type(), $args );

		$this->register_taxonomies();

		// Assign applicable taxonomies
		foreach ( $this->taxonomies as $taxonomy ) {
			register_taxonomy_for_object_type( $taxonomy, $this->get_post_type() );
		}
	}

	/**
	 * A place for the derivative class to implement additional hooks.
	 */
	function init() {}

	/**
	 * Register taxonomies.
	 *
	 * @action init
	 */
	function register_taxonomies() {}

	/**
	 * Register meta boxes for this post type.
	 *
	 * @action add_meta_boxes_[post_type]
	 */
	function register_meta_boxes() {}


	function save_post() {}

	/**
	 * Return the nonce key that corresponds to this post type.
	 */
	function nonce_key() {
		return $this->get_post_type() . '_nonce';
	}

	/**
	 * Add a nonce field.
	 */
	function nonce_field() {
		global $post;
		if ( $post->post_type == $this->get_post_type() ) {
			wp_nonce_field( $this->nonce_key(), $this->nonce_key(), false, true );
		}
	}

	/**
	 * Verify that the nonce is present and valid.
	 * @param  boolean $once unset the nonce field so it only gets checked once?
	 * @return boolean       validity
	 */
	function verify_nonce( $once = false ) {
		$check = isset( $_POST[ $this->nonce_key() ] ) && wp_verify_nonce( $_POST[ $this->nonce_key() ], $this->nonce_key() );
		// do we only want to do this once?
		if ( $once ) {
			unset( $_POST[ $this->nonce_key() ] );
		}
		return $check;
	}

}
