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

require_once plugin_dir_path( WSUID_PLUGIN_FILE ) . 'includes/class-wsuid-assets.php';

add_filter( 'woocommerce_admin_features', 'wsuid_maybe_enable_settings_ui' );
add_filter( 'woocommerce_get_settings_pages', 'wsuid_add_settings_page' );
add_action( 'admin_enqueue_scripts', array( 'WSUID_Assets', 'register' ) );
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

	if ( ! class_exists( 'WSUID_Settings_Page', false ) ) {
		require_once plugin_dir_path( WSUID_PLUGIN_FILE ) . 'includes/class-wsuid-settings-page-adapter.php';
		require_once plugin_dir_path( WSUID_PLUGIN_FILE ) . 'includes/class-wsuid-settings-page.php';
	}

	$settings_pages[] = new WSUID_Settings_Page();

	return $settings_pages;
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
