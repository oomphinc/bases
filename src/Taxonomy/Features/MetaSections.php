<?php

/**
 * Add meta form handling to a taxonomy
 */

namespace OomphInc\Bases\Taxonomy\Features;

/**
 * @uses $meta_sections  multidimensional array property that defines forms based on WP_Forms_API structure
 * @uses  ::get_name()
 */
trait MetaSections {

	use \OomphInc\Bases\Common\Nonce;

	protected function _init_MetaSections() {
		if ( !empty( $this->meta_sections ) ) {
			add_action( $this->get_name() . '_edit_form_fields', [ $this, '_render_meta_sections' ], 10, 2 );
			add_action( 'edit_terms', [ $this, '_save_term' ], 10, 2 );
		}
	}

	/**
	 * Prepare the meta forms property, if necessary.
	 */
	function prepare_meta_form() {}


	/**
	 * When a term is saved, check nonce and call save_meta().
	 *
	 * @action edit_terms
	 */
	function _save_term( $term_id, $taxonomy ) {
		if ( $taxonomy !== $this->get_name() || !$this->verify_nonce( true ) ) {
			return;
		}

		// munge the meta form
		$this->prepare_meta_form();
		\WP_Forms_API::process_form( $this->meta_sections, $meta_values );

		if ( isset( $meta_values ) ) {
			$this->replace_term_meta( $term_id, $meta_values );
		}

		// optional user-defined method
		$this->save_meta( $term_id, $taxonomy, $meta_values );
	}

	/**
	 * Derivative class can take additional action when meta is saved.
	 */
	function save_meta( $term_id, $taxonomy, $meta_values ) {}

	/**
	 * Munge and render the meta sections.
	 */
	function _render_meta_sections( $term, $taxonomy ) {
		$this->prepare_meta_form();
		$this->nonce_field();
		$values = [];

		// pluck out the meta values
		foreach ( get_term_meta( $term->term_id ) as $meta_key => $meta_values ) {
			$values[ $meta_key ] = maybe_unserialize( $meta_values[0] );
		}

		// render the sections
		foreach ( \WP_Forms_API::get_elements( $this->meta_sections ) as $key => $form ) {
			?>
			<tr class="form-field">
				<th scope="row"><?php echo esc_html( $form['#label'] ); ?></th>
				<td><?php echo \WP_Forms_API::render_form( $form, $values ); ?></td>
			</tr>
			<?php
		}
	}

	/**
	 * Replace meta values for a term. Any key in $values
	 * will have the corresponding termmeta key and value
	 * replaced.
	 * @param  int $term_id
	 * @param  array $values key value pairs of meta
	 */
	function replace_term_meta( $term_id, array $values ) {
		foreach ( $values as $meta_key => $meta_value ) {
			update_term_meta( $term_id, $meta_key, $meta_value );
		}
	}

}
