<?php

/**
 * Custom Post Type base class
 */

namespace OomphInc\Bases\Post;

use OomphInc\Bases\BaseEntity;

abstract class CustomPostType extends BaseEntity {

	// Any additional arguments to register_post_type
	protected $post_type_args = [];
	// Taxonomies that this post type can be assigned terms from
	protected $taxonomies = [];
	// Leaving $label_singular empty implies the CPT is registered by other means
	protected $label_singular;
	protected $label_plural;
	protected static $shared_hooks = [
		[ 'init', 'register_cpt', 1 ],
		[ 'init', 'handle_taxonomies', 2 ],
		[ 'save_post' ],
	];

	/**
	 * Register hooks n' stuff
	 */
	protected function __construct() {
		self::$shared_hooks[] = [ 'add_meta_boxes_' . $this->get_post_type(), 'register_meta_boxes' ];
		parent::__construct();
	}

	/**
	 * Get the post type this class is representing.
	 *
	 * @return string The post type slug, found in the 'post_type' class constant.
	 */
	function get_post_type() {
		return $this->get_name();
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
			'taxonomies' => [],
		] );

		register_post_type( $this->get_post_type(), $args );
	}

	/**
	 * Run any taxonomy creation and link up taxonomies.
	 */
	function handle_taxonomies() {
		$this->register_taxonomies();

		// Assign applicable taxonomies
		foreach ( $this->taxonomies as $taxonomy ) {
			register_taxonomy_for_object_type( $taxonomy, $this->get_post_type() );
		}
	}

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


	function save_post( $post_id, $post, $update ) {}

}
