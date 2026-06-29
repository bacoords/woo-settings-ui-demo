<?php
/**
 * Settings UI asset registration.
 *
 * @package WooSettingsUIDemo
 */

declare( strict_types=1 );

defined( 'ABSPATH' ) || exit;

/**
 * Registers scripts and styles used by the demo settings page.
 */
class WSUID_Assets {

	/**
	 * Register assets used by the Settings UI custom component.
	 */
	public static function register(): void {
		self::register_settings_ui_sdk_alias();

		$script_path       = plugin_dir_path( WSUID_PLUGIN_FILE ) . 'build/index.js';
		$script_asset_path = plugin_dir_path( WSUID_PLUGIN_FILE ) . 'build/index.asset.php';
		$script_asset      = file_exists( $script_asset_path )
			? require $script_asset_path
			: array(
				'dependencies' => array( 'react-jsx-runtime', 'wc-settings-ui-sdk', 'wp-components', 'wp-i18n' ),
				'version'      => file_exists( $script_path ) ? (string) filemtime( $script_path ) : WSUID_VERSION,
			);
		$style_path        = plugin_dir_path( WSUID_PLUGIN_FILE ) . 'build/style-index.css';

		wp_register_script(
			WSUID_SCRIPT_HANDLE,
			plugins_url( 'build/index.js', WSUID_PLUGIN_FILE ),
			is_array( $script_asset['dependencies'] ?? null ) ? $script_asset['dependencies'] : array(),
			is_string( $script_asset['version'] ?? null ) ? $script_asset['version'] : WSUID_VERSION,
			true
		);

		wp_register_style(
			WSUID_STYLE_HANDLE,
			plugins_url( 'build/style-index.css', WSUID_PLUGIN_FILE ),
			array(),
			file_exists( $style_path ) ? (string) filemtime( $style_path ) : WSUID_VERSION
		);
		wp_style_add_data( WSUID_STYLE_HANDLE, 'rtl', 'replace' );

		if ( self::is_demo_settings_screen() ) {
			wp_enqueue_style( WSUID_STYLE_HANDLE );
		}
	}

	/**
	 * Register a compatibility alias for the Settings UI SDK script handle.
	 *
	 * The current dependency extraction package maps @woocommerce/settings-ui-sdk
	 * to wc-settings-ui-sdk. WooCommerce 10.9.x still exposes this API through the
	 * wc-settings-ui handle and window.wc.settingsUi.
	 */
	private static function register_settings_ui_sdk_alias(): void {
		if ( wp_script_is( 'wc-settings-ui-sdk', 'registered' ) ) {
			return;
		}

		wp_register_script(
			'wc-settings-ui-sdk',
			false,
			array( 'wc-settings-ui' ),
			WSUID_VERSION,
			true
		);
		wp_add_inline_script(
			'wc-settings-ui-sdk',
			'window.wc = window.wc || {}; window.wc.settingsUiSdk = window.wc.settingsUiSdk || window.wc.settingsUi;',
			'after'
		);
	}

	/**
	 * Determine whether the current admin request is the demo settings page.
	 */
	private static function is_demo_settings_screen(): bool {
		if ( ! is_admin() ) {
			return false;
		}

		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Read-only current screen detection.
		$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';
		$tab  = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : '';
		// phpcs:enable WordPress.Security.NonceVerification.Recommended

		return 'wc-settings' === $page && WSUID_PAGE_ID === $tab;
	}
}
