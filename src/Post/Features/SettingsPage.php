<?php

/**
 * Add a settings page for a specific post type. These apply to the CPT as a whole,
 * for instance, for use on the CPT archive page.
 */

namespace OomphInc\Bases\Post\Features;

// prefix for options
const OPTIONS_PREFIX = 'CPT_settings_';

/**
 * @uses $settings_forms  multidimensional array property that defines forms based on WP_Forms_API structure
 */
trait SettingsPage {

	private $_page;

	protected function _init_SettingsPage() {
		if ( !empty( $this->settings_form ) ) {
			$this->_page = new \OomphInc\Bases\Admin\SettingsPage( OPTIONS_PREFIX . $this->get_post_type(), [
				'parent' => 'edit.php' . ( $this->get_post_type() !== 'post' ? '?post_type=' . $this->get_post_type() : '' ),
				'form' => $this->settings_form,
			] );
		}
	}

	/**
	 * Retrieve any settings saved to this CPT.
	 * @return array settings
	 */
	function get_settings() {
		return $this->_page->get_settings();
	}

}
