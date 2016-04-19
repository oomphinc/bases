<?php

/**
 * Base class for menu or submenu pages.
 */

namespace OomphInc\Bases\Admin;

class MenuPage extends \OomphInc\Bases\InstantiableEntity {

	protected $title = 'Page';
	// this dictates whether the page will be a submenu or regular menu page
	protected $parent = 'options-general.php';
	protected $capability = 'manage_options';
	// for top level pages
	protected $icon = '';
	protected $position;
	protected $render; // optional callable used to render the page
	protected static $shared_hooks = [
		[ 'admin_menu', 'register_page' ],
	];

	/**
	 * Register the admin page for displaying the settings.
	 *
	 * @action admin_menu
	 */
	function register_page() {
		$args = [
			$this->title,
			$this->title,
			$this->capability,
			$this->get_name(),
			is_callable( $this->render ) ? $this->render : [ $this, 'render' ],
		];
		// submenu
		if ( $this->parent ) {
			array_unshift( $args, $this->parent );
			$func = 'add_submenu_page';
		// regular menu page
		} else {
			array_push( $args, $this->icon, $this->position );
			$func = 'add_menu_page';
		}
		call_user_func_array( $func, $args );
	}

	/**
	 * Render the page.
	 */
	function render() {}

}
