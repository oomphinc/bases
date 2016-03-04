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

	private $_settings_title;

	protected function _init_SettingsPage() {
		if ( !empty( $this->settings_form ) ) {
			add_action( 'admin_menu', [ $this, '_register_settings_page' ] );
			add_action( 'admin_init', [ $this, '_register_setting' ] );
			$this->_settings_title = !empty( $this->settings_title ) ? $this->settings_title : 'Settings';
		}
	}
	// form fields to display on a settings page under the CPT section in WP Admin

	/**
	 * Register the admin page for displaying the settings.
	 *
	 * @action admin_menu
	 */
	function _register_settings_page() {
		add_submenu_page(
			'edit.php?post_type=' . $this->get_post_type(),
			$this->_settings_title,
			$this->_settings_title,
			!empty( $this->settings_capability ) ? $this->settings_capability : 'manage_options',
			OPTIONS_PREFIX . $this->get_post_type(),
			[ $this, '_render_settings_page' ]
		);
	}

	/**
	 * Retrieve any settings saved to this CPT.
	 * @return array settings
	 */
	function get_settings() {
		return get_option( OPTIONS_PREFIX . $this->get_post_type(), [] );
	}

	/**
	 * Handle the POST submission of settings from the form.
	 * @return array processed values
	 */
	function _process_settings() {
		WP_Forms_API::process_form( $this->settings_form, $settings_values );
		return isset( $settings_values ) ? $settings_values : [];
	}

	/**
	 * Register a setting for this CPT.
	 *
	 * @action admin_init
	 */
	function _register_setting() {
		register_setting( OPTIONS_PREFIX . $this->get_post_type(), OPTIONS_PREFIX . $this->get_post_type(), [ $this, '_process_settings' ] );
	}

	/**
	 * Render the settings form page.
	 */
	function _render_settings_page() {
		?>
		<div class="wrap">
			<h1><?php echo esc_html( $this->_settings_title ); ?></h1>
			<form method="post" action="options.php">
			<?php
			settings_fields( OPTIONS_PREFIX . $this->get_post_type() );
			$settings = $this->get_settings();
			echo WP_Forms_API::render_form( $this->settings_form, $settings );
			submit_button();
			?>
			</form>
		</div>
		<?php
	}

}
