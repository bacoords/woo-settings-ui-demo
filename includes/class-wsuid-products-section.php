<?php
/**
 * Demo registered Products settings section.
 *
 * @package WooSettingsUIDemo
 */

declare( strict_types=1 );

defined( 'ABSPATH' ) || exit;

/**
 * Adds a demo section to WooCommerce's existing Products settings page.
 */
class WSUID_Products_Section extends \Automattic\WooCommerce\Admin\Settings\SettingsSection {

	/**
	 * Get the parent WooCommerce settings page id.
	 */
	public function get_parent_page_id(): string {
		return 'products';
	}

	/**
	 * Get the section id.
	 */
	public function get_id(): string {
		return WSUID_PRODUCTS_SECTION_ID;
	}

	/**
	 * Get the section label.
	 */
	public function get_label(): string {
		return __( 'Settings UI Demo', 'woo-settings-ui-demo' );
	}

	/**
	 * Get legacy settings for this section.
	 *
	 * @param WC_Settings_Page $parent_page Parent settings page.
	 * @return array<mixed>
	 */
	public function get_settings( WC_Settings_Page $parent_page ): array {
		return array(
			array(
				'title' => __( 'Registered Products section', 'woo-settings-ui-demo' ),
				'type'  => 'title',
				'desc'  => __( 'This section is registered under WooCommerce\'s existing Products settings page with the SettingsSection API.', 'woo-settings-ui-demo' ),
				'id'    => 'wsuid_products_section',
			),
			array(
				'title'   => __( 'Enable product note', 'woo-settings-ui-demo' ),
				'desc'    => __( 'A checkbox saved from a registered section on the Products settings page.', 'woo-settings-ui-demo' ),
				'id'      => 'wsuid_products_section_enabled',
				'default' => 'no',
				'type'    => 'checkbox',
			),
			array(
				'title'       => __( 'Product note', 'woo-settings-ui-demo' ),
				'desc'        => __( 'A text option that uses the parent Products settings page save flow.', 'woo-settings-ui-demo' ),
				'id'          => 'wsuid_products_section_note',
				'type'        => 'text',
				'default'     => __( 'Shown from a registered Products section.', 'woo-settings-ui-demo' ),
				'placeholder' => __( 'Example: Shown from a registered Products section.', 'woo-settings-ui-demo' ),
			),
			array(
				'title'   => __( 'Catalog placement', 'woo-settings-ui-demo' ),
				'desc'    => __( 'A select field rendered from the registered Products section.', 'woo-settings-ui-demo' ),
				'id'      => 'wsuid_products_section_placement',
				'type'    => 'select',
				'default' => 'catalog',
				'options' => array(
					'catalog' => __( 'Product catalog', 'woo-settings-ui-demo' ),
					'detail'  => __( 'Product detail pages', 'woo-settings-ui-demo' ),
					'both'    => __( 'Catalog and detail pages', 'woo-settings-ui-demo' ),
				),
			),
			array(
				'type' => 'sectionend',
				'id'   => 'wsuid_products_section',
			),
		);
	}
}
