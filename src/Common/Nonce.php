<?php

namespace OomphInc\Bases\Common;

/**
 * @uses ::get_name()
 */
trait Nonce {

	/**
	 * Return the nonce key that corresponds to this post type.
	 */
	function nonce_key() {
		return $this->get_name() . '_nonce';
	}

	/**
	 * Add a nonce field.
	 */
	function nonce_field() {
		wp_nonce_field( $this->nonce_key(), $this->nonce_key(), false, true );
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
