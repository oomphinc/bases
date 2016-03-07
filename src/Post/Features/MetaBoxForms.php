<?php

/**
 * Add metabox form handling to a CPT class
 */

namespace OomphInc\Bases\Post\Features;

/**
 * @uses $meta_forms  multidimensional array property that defines forms based on WP_Forms_API structure
 */
trait MetaBoxForms {

	use \OomphInc\Bases\Common\Nonce;

	protected function _init_MetaBoxForms() {
		if ( !empty( $this->meta_forms ) ) {
			add_action( 'add_meta_boxes_' . $this->get_post_type(), [ $this, '_register_meta_boxes' ] );
			add_action( 'save_post', [ $this, '_save_meta' ] );
		}
	}

	/**
	 * Register meta boxes for this CPT.
	 *
	 * @action add_meta_boxes_[post_type]
	 */
	function _register_meta_boxes( $post ) {
		add_action( 'post_submitbox_misc_actions', [ $this, '_add_nonce' ] );
		add_action( 'attachment_submitbox_misc_actions', [ $this, '_add_nonce' ] );

		$this->prepare_meta_form();

		// each element in $this->meta_forms gets its own meta box
		foreach ( \WP_Forms_API::get_elements( $this->meta_forms ) as $key => $form ) {
			$form += [ '#context' => 'normal', '#priority' => 'default' ];

			add_meta_box( $this->get_post_type() . '-' . $key, $form['#label'], [ $this, '_meta_boxes' ], $this->get_post_type(), $form['#context'], $form['#priority'], $form );
		}

		// Allow subclasses to register additional meta box
		$this->register_meta_boxes( $post );
	}

	/**
	 * Add the nonce field on post pages that use meta box forms.
	 */
	function _add_nonce() {
		global $post;
		if ( $post->post_type === $this->get_post_type() ) {
			$this->nonce_field();
		}
	}

	/**
	 * Render the meta boxes in $meta_forms
	 */
	function _meta_boxes( $post, $args ) {
		global $post;
		$this->_render_meta_form( $args['args'], $post );
	}

	/**
	 * Prepare the meta forms property, if necessary.
	 */
	function prepare_meta_form() {}


	/**
	 * When a post is saved, check nonce and call save_meta() if possible
	 *
	 * @action save_post
	 */
	function _save_meta( $post_id ) {
		$post = get_post( $post_id );

		if ( $post->post_type !== $this->get_post_type() || !$this->verify_nonce( true ) ) {
			return;
		}

		// munge the meta form
		$this->prepare_meta_form();
		\WP_Forms_API::process_form( $this->meta_forms, $meta_values );

		if ( isset( $meta_values ) ) {
			$this->replace_post_meta( $post->ID, $meta_values );
		}

		// optional user-defined method
		$this->save_meta( $post, $meta_values );
	}

	/**
	 * Derivative class can take additional action when meta is saved.
	 */
	function save_meta( $post, $meta_values ) {}

	/**
	 * Munge and render a metadata form.
	 */
	protected function _render_meta_form( $form, $post ) {
		$post = get_post( $post );
		$meta = get_post_custom( $post->ID );
		$values = [];

		foreach ( $meta as $meta_key => $meta_values ) {
			$values[ $meta_key ] = maybe_unserialize( $meta_values[0] );
		}

		echo \WP_Forms_API::render_form( $form, $values );
	}

	/**
	 * Replace meta values for a post. Any key in $values
	 * will have the corresponding postmeta key and value
	 * replaced.
	 * @param  mixed $post   whatever get_post accepts
	 * @param  array $values key value pairs of meta
	 */
	function replace_post_meta( $post, array $values ) {
		$post = get_post( $post );

		foreach ( $values as $meta_key => $meta_value ) {
			update_post_meta( $post->ID, $meta_key, $meta_value );
		}
	}

}
