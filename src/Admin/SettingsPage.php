<?php

/**
 * Settings page base class.
 */

namespace OomphInc\Bases\Admin;

use WP_Forms_API;

class SettingsPage extends MenuPage {

	protected $title = 'Settings';
	public $form = [];
	protected static $shared_hooks = [
		[ 'admin_menu', 'register_page' ],
		[ 'admin_init', 'register_setting' ],
	];

	/**
	 * Retrieve any settings saved to this page.
	 * @return array settings
	 */
	function get_settings() {
		return get_option( $this->get_name(), [] );
	}

	/**
	 * Handle the POST submission of settings from the form.
	 * @return array processed values
	 */
	function process_settings() {
		WP_Forms_API::process_form( $this->form, $settings_values );
		return isset( $settings_values ) ? $settings_values : [];
	}

	/**
	 * Register a setting for this page.
	 *
	 * @action admin_init
	 */
	function register_setting() {
		register_setting( $this->get_name(), $this->get_name(), [ $this, 'process_settings' ] );
	}

	/**
	 * Render the settings form page.
	 */
	function render() {
		?>
		<div class="wrap">
			<h1><?php echo esc_html( $this->title ); ?></h1>
			<form method="post" action="options.php">
			<?php
			settings_fields( $this->get_name() );
			$settings = $this->get_settings();
			echo WP_Forms_API::render_form( $this->form, $settings );
			submit_button();
			?>
			</form>
		</div>
		<?php
	}

}
