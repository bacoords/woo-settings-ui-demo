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
define( 'WSUID_PAGE_ID', 'settings_ui_demo' );
define( 'WSUID_FEATURE_OPTION', 'wsuid_enable_settings_ui' );
define( 'WSUID_SCRIPT_HANDLE', 'woo-settings-ui-demo-settings' );
define( 'WSUID_STYLE_HANDLE', 'woo-settings-ui-demo-settings' );

add_filter( 'woocommerce_admin_features', 'wsuid_maybe_enable_settings_ui' );
add_filter( 'woocommerce_get_settings_pages', 'wsuid_add_settings_page' );
add_action( 'admin_enqueue_scripts', 'wsuid_register_settings_ui_assets' );
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

	$settings_pages[] = new WSUID_Settings_Page();

	return $settings_pages;
}

/**
 * Declare the WooCommerce settings page classes once WooCommerce has loaded.
 */
function wsuid_declare_settings_page_classes(): void {
	if ( class_exists( 'WSUID_Settings_Page', false ) ) {
		return;
	}

	require_once plugin_dir_path( WSUID_PLUGIN_FILE ) . 'includes/class-wsuid-settings-page-adapter.php';
	require_once plugin_dir_path( WSUID_PLUGIN_FILE ) . 'includes/class-wsuid-settings-page.php';
}

/**
 * Build the demo settings array used by both the legacy renderer and Settings UI.
 *
 * @return array<mixed>
 */
function wsuid_get_demo_settings(): array {
	$settings_ui_enabled = 'yes' === get_option( WSUID_FEATURE_OPTION, 'no' );
	$status_label        = $settings_ui_enabled ? __( 'enabled', 'woo-settings-ui-demo' ) : __( 'disabled', 'woo-settings-ui-demo' );

	return array(
		array(
			'title'   => __( 'Settings UI demo', 'woo-settings-ui-demo' ),
			'type'    => 'title',
			'desc'    => sprintf(
				/* translators: %s: WooCommerce Settings UI documentation URL. */
				__( 'This page uses the normal WooCommerce settings array and save flow. Turn the feature flag on to render this same page with the React Settings UI. <a href="%s" target="_blank" rel="noreferrer noopener">Read the Settings UI docs</a>.', 'woo-settings-ui-demo' ),
				esc_url( 'https://developer.woocommerce.com/docs/extensions/settings-and-config/settings-ui/' )
			),
			'actions' => array(
				array(
					'id'      => 'products-reference',
					'label'   => __( 'View Products settings', 'woo-settings-ui-demo' ),
					'href'    => admin_url( 'admin.php?page=wc-settings&tab=products' ),
					'variant' => 'secondary',
				),
			),
			'id'      => 'wsuid_demo_options',
		),
		array(
			'title'         => __( 'Enable Settings UI renderer', 'woo-settings-ui-demo' ),
			'desc'          => __( 'Render opted-in WooCommerce settings pages with the React Settings UI.', 'woo-settings-ui-demo' ),
			'id'            => WSUID_FEATURE_OPTION,
			'default'       => 'no',
			'type'          => 'checkbox',
			'checkboxgroup' => 'start',
		),
		array(
			'desc'          => __( 'Keep saving through the existing WooCommerce settings form flow.', 'woo-settings-ui-demo' ),
			'id'            => 'wsuid_preserve_legacy_save_flow',
			'default'       => 'yes',
			'type'          => 'checkbox',
			'checkboxgroup' => 'end',
		),
		array(
			'title'       => __( 'Storefront message', 'woo-settings-ui-demo' ),
			'desc'        => __( 'A plain text option rendered by native controls in both modes.', 'woo-settings-ui-demo' ),
			'id'          => 'wsuid_storefront_message',
			'type'        => 'text',
			'default'     => __( 'Free shipping over $75', 'woo-settings-ui-demo' ),
			'placeholder' => __( 'Example: Free shipping over $75', 'woo-settings-ui-demo' ),
		),
		array(
			'title'   => __( 'Display mode', 'woo-settings-ui-demo' ),
			'desc'    => __( 'A native select field for comparing labels, help text, and saves.', 'woo-settings-ui-demo' ),
			'id'      => 'wsuid_display_mode',
			'type'    => 'select',
			'default' => 'compact',
			'options' => array(
				'compact'  => __( 'Compact', 'woo-settings-ui-demo' ),
				'balanced' => __( 'Balanced', 'woo-settings-ui-demo' ),
				'detailed' => __( 'Detailed', 'woo-settings-ui-demo' ),
			),
		),
		array(
			'title'             => __( 'Reminder delay', 'woo-settings-ui-demo' ),
			'desc'              => __( 'A number field with the same option id in both renderers.', 'woo-settings-ui-demo' ),
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
			'title'     => __( 'Notification channels', 'woo-settings-ui-demo' ),
			'desc'      => __( 'Legacy mode renders this as a native multiselect. Settings UI mode renders the same saved option with a custom component.', 'woo-settings-ui-demo' ),
			'id'        => 'wsuid_notification_channels',
			'type'      => 'multiselect',
			'default'   => array( 'email', 'dashboard' ),
			'component' => 'woo-settings-ui-demo/channel-picker',
			'options'   => array(
				'email'     => __( 'Email', 'woo-settings-ui-demo' ),
				'dashboard' => __( 'Dashboard inbox', 'woo-settings-ui-demo' ),
				'sms'       => __( 'SMS', 'woo-settings-ui-demo' ),
				'webhook'   => __( 'Webhook', 'woo-settings-ui-demo' ),
			),
		),
		array(
			'title'       => __( 'Internal notes', 'woo-settings-ui-demo' ),
			'desc'        => __( 'A textarea option for checking multiline values in both renderers.', 'woo-settings-ui-demo' ),
			'id'          => 'wsuid_internal_notes',
			'type'        => 'textarea',
			'default'     => '',
			'placeholder' => __( 'Add notes for testing save behavior.', 'woo-settings-ui-demo' ),
		),
		array(
			'type' => 'sectionend',
			'id'   => 'wsuid_demo_options',
		),
		array(
			'title' => __( 'Diagnostics', 'woo-settings-ui-demo' ),
			'type'  => 'title',
			'desc'  => __( 'This display-only field uses the Settings UI none save adapter and is ignored by the normal settings save routine.', 'woo-settings-ui-demo' ),
			'id'    => 'wsuid_demo_diagnostics',
		),
		array(
			'title' => __( 'Current renderer state', 'woo-settings-ui-demo' ),
			'type'  => 'info',
			'id'    => 'wsuid_renderer_state',
			'text'  => sprintf(
				/* translators: %s: enabled or disabled. */
				__( 'The saved Settings UI feature flag is currently %s.', 'woo-settings-ui-demo' ),
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
 * Register assets used by the Settings UI custom component.
 */
function wsuid_register_settings_ui_assets(): void {
	$script_path = plugin_dir_path( WSUID_PLUGIN_FILE ) . 'assets/settings-ui-demo.js';
	$style_path  = plugin_dir_path( WSUID_PLUGIN_FILE ) . 'assets/settings-ui-demo.css';

	wp_register_script(
		WSUID_SCRIPT_HANDLE,
		plugins_url( 'assets/settings-ui-demo.js', WSUID_PLUGIN_FILE ),
		array( 'wc-settings-ui', 'wp-components', 'wp-element', 'wp-i18n' ),
		file_exists( $script_path ) ? (string) filemtime( $script_path ) : WSUID_VERSION,
		true
	);

	wp_register_style(
		WSUID_STYLE_HANDLE,
		plugins_url( 'assets/settings-ui-demo.css', WSUID_PLUGIN_FILE ),
		array(),
		file_exists( $style_path ) ? (string) filemtime( $style_path ) : WSUID_VERSION
	);

	if ( wsuid_is_demo_settings_screen() ) {
		wp_enqueue_style( WSUID_STYLE_HANDLE );
	}
}

/**
 * Determine whether the current admin request is the demo settings page.
 */
function wsuid_is_demo_settings_screen(): bool {
	if ( ! is_admin() ) {
		return false;
	}

	// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Read-only current screen detection.
	$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';
	$tab  = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : '';
	// phpcs:enable WordPress.Security.NonceVerification.Recommended

	return 'wc-settings' === $page && WSUID_PAGE_ID === $tab;
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
		esc_html__( 'Woo Settings UI Demo requires WooCommerce to be active.', 'woo-settings-ui-demo' )
	);
}
