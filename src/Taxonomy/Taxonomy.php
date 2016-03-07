<?php

/**
 * Taxonomy base class
 */

namespace OomphInc\Bases\Taxonomy;

use OomphInc\Bases\BaseEntity;

abstract class Taxonomy extends BaseEntity {

	// Any additional arguments to register_taxonomy
	protected $taxonomy_args = [];
	// Post types to assign this taxonomy to
	protected $post_types = null;
	// Leaving $label_singular empty implies the CPT is registered by other means
	protected $label_singular;
	protected $label_plural;
	protected static $shared_hooks = [
		[ 'init', 'register_tax', 0 ],
	];

	/**
	 * Register taxonomy, but only if $this->label_singular is set.
	 *
	 * @action init
	 */
	function register_tax() {
		if ( empty( $this->label_singular ) ) {
			return;
		}
		else if ( empty( $this->label_plural ) ) {
			$this->label_plural = $this->label_singular . 's';
		}
		$args = wp_parse_args( $this->taxonomy_args, [
			'labels' => [
				'name' => $this->label_plural,
				'singular_name' => $this->label_singular,
				'all_items' => 'All ' . $this->label_plural,
				'add_new_item' => 'Add New ' . $this->label_singular,
				'edit_item' => 'Edit ' . $this->label_singular,
				'new_item' => 'New ' . $this->label_singular,
				'new_item_name' => 'New ' . $this->label_singular . ' Name',
				'parent_item' => 'Parent ' . $this->label_singular,
				'parent_item_colon' => 'Parent ' . $this->label_singular . ':',
				'view_item' => 'View ' . $this->label_singular,
				'search_items' => 'Search ' . $this->label_plural,
				'popular_items' => 'Popular ' . $this->label_plural,
				'not_found' => 'No ' . strtolower( $this->label_plural ) . ' found',
				'separate_items_with_commas' => 'Separate ' . strtolower( $this->label_plural ) . ' with commas',
				'add_or_remove_items' => 'Add or remove ' . strtolower( $this->label_plural ),
				'choose_from_most_used' => 'Choose from the most used ' . strtolower( $this->label_plural ),
			],
			'show_ui' => true,
			'public' => true,
			'show_tagcloud' => false,
			'show_in_nav_menus' => true,
			'hierarchical' => false,
			'rewrite' => [ 'slug' => $this->get_name(), 'with_front' => false, 'hierarchical' => true ],
		] );

		register_taxonomy( $this->get_name(), $this->post_types, $args );
	}

	/**
	 * Register meta boxes for this taxonomy.
	 *
	 * @action add_meta_boxes_[post_type]
	 */
	function register_meta_boxes() {}

}
