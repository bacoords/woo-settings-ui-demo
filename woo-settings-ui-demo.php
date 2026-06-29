<?php
/**
 * Plugin Name: Woo Settings UI Demo
 * Plugin URI: https://developer.woocommerce.com/docs/extensions/settings-and-config/settings-ui/
 * Description: Demonstrates opting a WooCommerce settings page into the Settings UI renderer while preserving the legacy settings flow.
 * Version: 0.1.0
 * Author: Woo Developer Advocacy
 * Text Domain: woo-settings-ui-demo
 * Requires Plugins: woocommerce
 * Requires PHP: 7.4
 *
 * @package WooSettingsUIDemo
 */

declare( strict_types=1 );

defined( 'ABSPATH' ) || exit;

define( 'WSUID_VERSION', '0.1.0' );
define( 'WSUID_PLUGIN_FILE', __FILE__ );
define( 'WSUID_TEXT_DOMAIN', 'woo-settings-ui-demo' );
define( 'WSUID_PAGE_ID', 'settings_ui_demo' );
define( 'WSUID_FEATURE_OPTION', 'wsuid_enable_settings_ui' );

add_filter( 'woocommerce_admin_features', 'wsuid_maybe_enable_settings_ui' );
add_filter( 'woocommerce_get_settings_pages', 'wsuid_add_settings_page' );
add_action( 'admin_notices', 'wsuid_maybe_show_woocommerce_missing_notice' );

/**
 * Enable WooCommerce's experimental Settings UI feature when the demo setting is on.
 *
 * @param array<mixed> $features Registered WooCommerce admin features.
 * @return array<mixed>
 */
function wsuid_maybe_enable_settings_ui( array $features ): array {
	if ( 'yes' !== get_option( WSUID_FEATURE_OPTION, 'no' ) ) {
		return $features;
	}

	$features[] = 'settings-ui';

	return array_values( array_unique( $features ) );
}

/**
 * Register the demo settings page with WooCommerce.
 *
 * @param array<mixed> $settings_pages WooCommerce settings page instances.
 * @return array<mixed>
 */
function wsuid_add_settings_page( array $settings_pages ): array {
	if (
		! class_exists( 'WC_Settings_Page' )
		|| ! class_exists( '\Automattic\WooCommerce\Admin\Settings\LegacySettingsPageAdapter' )
		|| ! interface_exists( '\Automattic\WooCommerce\Admin\Settings\SettingsUIPageInterface' )
	) {
		return $settings_pages;
	}

	wsuid_declare_settings_page_classes();

	$settings_pages[] = new WC_Settings_UI_Demo_Page();

	return $settings_pages;
}

/**
 * Declare the WooCommerce settings page classes once WooCommerce has loaded.
 */
function wsuid_declare_settings_page_classes(): void {
	if ( class_exists( 'WC_Settings_UI_Demo_Page', false ) ) {
		return;
	}

	/**
	 * Adds Settings UI shell metadata to the legacy adapter.
	 */
	class WC_Settings_UI_Demo_Page_Adapter extends \Automattic\WooCommerce\Admin\Settings\LegacySettingsPageAdapter {

		/**
		 * Build the Settings UI schema for the current section.
		 *
		 * @param string $section Section id. Empty string means the default section.
		 * @return array<mixed>
		 */
		public function get_schema( string $section ): array {
			$schema = parent::get_schema( $section );

			$schema['shell']['subtitle'] = __( 'Toggle WooCommerce Settings UI rendering and compare native fields across both renderers.', WSUID_TEXT_DOMAIN );
			$schema['shell']['badges']   = array(
				array(
					'label'  => __( 'Settings UI active', WSUID_TEXT_DOMAIN ),
					'intent' => 'success',
				),
			);

			return $schema;
		}
	}

	/**
	 * Demo settings tab.
	 */
	class WC_Settings_UI_Demo_Page extends WC_Settings_Page {

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->id    = WSUID_PAGE_ID;
			$this->label = __( 'Settings UI Demo', WSUID_TEXT_DOMAIN );
			$this->icon  = 'admin-customizer';

			parent::__construct();
		}

		/**
		 * Opt this settings page into WooCommerce's Settings UI renderer.
		 *
		 * WooCommerce only uses this adapter when the settings-ui feature flag is enabled.
		 *
		 * @return \Automattic\WooCommerce\Admin\Settings\SettingsUIPageInterface|null
		 */
		public function get_settings_ui_page(): ?\Automattic\WooCommerce\Admin\Settings\SettingsUIPageInterface {
			return new WC_Settings_UI_Demo_Page_Adapter( $this );
		}

		/**
		 * Get settings for the default section.
		 *
		 * @return array<mixed>
		 */
		protected function get_settings_for_default_section() {
			return wsuid_get_demo_settings();
		}
	}
}

/**
 * Build the demo settings array used by both the legacy renderer and Settings UI.
 *
 * @return array<mixed>
 */
function wsuid_get_demo_settings(): array {
	$settings_ui_enabled = 'yes' === get_option( WSUID_FEATURE_OPTION, 'no' );
	$status_label        = $settings_ui_enabled ? __( 'enabled', WSUID_TEXT_DOMAIN ) : __( 'disabled', WSUID_TEXT_DOMAIN );

	return array(
		array(
			'title'   => __( 'Settings UI demo', WSUID_TEXT_DOMAIN ),
			'type'    => 'title',
			'desc'    => sprintf(
				/* translators: %s: WooCommerce Settings UI documentation URL. */
				__( 'This page uses the normal WooCommerce settings array and save flow. Turn the feature flag on to render this same page with the React Settings UI. <a href="%s" target="_blank" rel="noreferrer noopener">Read the Settings UI docs</a>.', WSUID_TEXT_DOMAIN ),
				esc_url( 'https://developer.woocommerce.com/docs/extensions/settings-and-config/settings-ui/' )
			),
			'actions' => array(
				array(
					'id'      => 'products-reference',
					'label'   => __( 'View Products settings', WSUID_TEXT_DOMAIN ),
					'href'    => admin_url( 'admin.php?page=wc-settings&tab=products' ),
					'variant' => 'secondary',
				),
			),
			'id'      => 'wsuid_demo_options',
		),
		array(
			'title'         => __( 'Enable Settings UI renderer', WSUID_TEXT_DOMAIN ),
			'desc'          => __( 'Render opted-in WooCommerce settings pages with the React Settings UI.', WSUID_TEXT_DOMAIN ),
			'id'            => WSUID_FEATURE_OPTION,
			'default'       => 'no',
			'type'          => 'checkbox',
			'checkboxgroup' => 'start',
		),
		array(
			'desc'          => __( 'Keep saving through the existing WooCommerce settings form flow.', WSUID_TEXT_DOMAIN ),
			'id'            => 'wsuid_preserve_legacy_save_flow',
			'default'       => 'yes',
			'type'          => 'checkbox',
			'checkboxgroup' => 'end',
		),
		array(
			'title'       => __( 'Storefront message', WSUID_TEXT_DOMAIN ),
			'desc'        => __( 'A plain text option rendered by native controls in both modes.', WSUID_TEXT_DOMAIN ),
			'id'          => 'wsuid_storefront_message',
			'type'        => 'text',
			'default'     => __( 'Free shipping over $75', WSUID_TEXT_DOMAIN ),
			'placeholder' => __( 'Example: Free shipping over $75', WSUID_TEXT_DOMAIN ),
		),
		array(
			'title'   => __( 'Display mode', WSUID_TEXT_DOMAIN ),
			'desc'    => __( 'A native select field for comparing labels, help text, and saves.', WSUID_TEXT_DOMAIN ),
			'id'      => 'wsuid_display_mode',
			'type'    => 'select',
			'default' => 'compact',
			'options' => array(
				'compact'  => __( 'Compact', WSUID_TEXT_DOMAIN ),
				'balanced' => __( 'Balanced', WSUID_TEXT_DOMAIN ),
				'detailed' => __( 'Detailed', WSUID_TEXT_DOMAIN ),
			),
		),
		array(
			'title'             => __( 'Reminder delay', WSUID_TEXT_DOMAIN ),
			'desc'              => __( 'A number field with the same option id in both renderers.', WSUID_TEXT_DOMAIN ),
			'id'                => 'wsuid_reminder_delay',
			'type'              => 'number',
			'default'           => '3',
			'custom_attributes' => array(
				'min'  => '1',
				'max'  => '14',
				'step' => '1',
			),
		),
		array(
			'title'   => __( 'Notification channels', WSUID_TEXT_DOMAIN ),
			'desc'    => __( 'This starts as a native multiselect. The next demo step replaces it with a custom Settings UI component.', WSUID_TEXT_DOMAIN ),
			'id'      => 'wsuid_notification_channels',
			'type'    => 'multiselect',
			'default' => array( 'email', 'dashboard' ),
			'options' => array(
				'email'     => __( 'Email', WSUID_TEXT_DOMAIN ),
				'dashboard' => __( 'Dashboard inbox', WSUID_TEXT_DOMAIN ),
				'sms'       => __( 'SMS', WSUID_TEXT_DOMAIN ),
				'webhook'   => __( 'Webhook', WSUID_TEXT_DOMAIN ),
			),
		),
		array(
			'title'       => __( 'Internal notes', WSUID_TEXT_DOMAIN ),
			'desc'        => __( 'A textarea option for checking multiline values in both renderers.', WSUID_TEXT_DOMAIN ),
			'id'          => 'wsuid_internal_notes',
			'type'        => 'textarea',
			'default'     => '',
			'placeholder' => __( 'Add notes for testing save behavior.', WSUID_TEXT_DOMAIN ),
		),
		array(
			'type' => 'sectionend',
			'id'   => 'wsuid_demo_options',
		),
		array(
			'title' => __( 'Diagnostics', WSUID_TEXT_DOMAIN ),
			'type'  => 'title',
			'desc'  => __( 'This display-only field uses the Settings UI none save adapter and is ignored by the normal settings save routine.', WSUID_TEXT_DOMAIN ),
			'id'    => 'wsuid_demo_diagnostics',
		),
		array(
			'title' => __( 'Current renderer state', WSUID_TEXT_DOMAIN ),
			'type'  => 'info',
			'id'    => 'wsuid_renderer_state',
			'text'  => sprintf(
				/* translators: %s: enabled or disabled. */
				__( 'The saved Settings UI feature flag is currently %s.', WSUID_TEXT_DOMAIN ),
				'<strong>' . esc_html( $status_label ) . '</strong>'
			),
			'save'  => array(
				'adapter' => 'none',
			),
		),
		array(
			'type' => 'sectionend',
			'id'   => 'wsuid_demo_diagnostics',
		),
	);
}

/**
 * Show a dependency notice if WooCommerce is not active.
 */
function wsuid_maybe_show_woocommerce_missing_notice(): void {
	if ( class_exists( 'WooCommerce' ) ) {
		return;
	}

	printf(
		'<div class="notice notice-error"><p>%s</p></div>',
		esc_html__( 'Woo Settings UI Demo requires WooCommerce to be active.', WSUID_TEXT_DOMAIN )
	);
}
