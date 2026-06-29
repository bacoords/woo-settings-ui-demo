<?php
/**
 * Demo WooCommerce settings page.
 *
 * @package WooSettingsUIDemo
 */

declare( strict_types=1 );

defined( 'ABSPATH' ) || exit;

/**
 * Demo settings tab.
 */
class WSUID_Settings_Page extends WC_Settings_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id    = WSUID_PAGE_ID;
		$this->label = __( 'Settings UI Demo', 'woo-settings-ui-demo' );
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
		return new WSUID_Settings_Page_Adapter( $this );
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
