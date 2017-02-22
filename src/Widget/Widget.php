<?php

/**
 * Taxonomy base class
 */

namespace OomphInc\Bases\Widget;

use WP_Forms_API;

abstract class Widget extends \WP_Widget {

	protected $form = [];
	protected $widget_name = '';

	public function __construct() {
		$this->pre_init();
		// follow ID base pattern used in core but without slashes
		$id_base = str_replace( '\\', '-', strtolower( get_class( $this ) ) );
		parent::__construct( $id_base, $this->widget_name );
		$this->init();
	}

	/**
	 * Add an action to register the widget class at the appropriate time.
	 */
	static public function register() {
		static $registered = false;

		// only register once!
		if ( $registered ) {
			return;
		}

		// who's calling?
		$class_name = get_called_class();

		add_action( 'widgets_init', function() use ( $class_name ) {
			register_widget( $class_name );
		} );
		add_action( 'admin_footer', [ __CLASS__, 'forms_js' ] );

		$registered = true;
	}

	/**
	 * Small piece of JS to make sure all WP Forms API fields work with widgets.
	 *
	 * @action admin_footer
	 */
	static public function forms_js() {
		?>
		<script type="text/javascript">
			jQuery(function($) {
				// when widgets are added or updated
				$(document).on('widget-added widget-updated', function(ev, widget) {
					if (typeof wpFormsApi === 'undefined') {
						return;
					}
					wpFormsApi.setup(widget);
				});
			});
		</script>
		<?php
	}

	/**
	 * To be optionally overridden.
	 */
	public function pre_init() { }
	public function init() { }

	/**
	 * Outputs the options form on admin.
	 *
	 * @param array $instance The widget options
	 */
	public function form( $instance ) {
		$form = $this->form;
		// add the widget specific id and name fields
		foreach ( $form as $key => &$field ) {
			$field['#id'] = $this->get_field_id( $key );
			$field['#name'] = $this->get_field_name( $key );
		}
		echo WP_Forms_API::render_form( $form, $instance );
	}

	/**
	 * Process widget options on save.
	 *
	 * @param array $new_instance The new options
	 * @param array $old_instance The previous options
	 */
	public function update( $new_instance, $old_instance ) {
		// $instance will hold the validated values, if successful
		WP_Forms_API::process_form( $this->form, $instance, $new_instance );
		return isset( $instance ) ? $instance : [];
	}
}
