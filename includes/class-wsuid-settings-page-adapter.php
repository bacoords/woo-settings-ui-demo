<?php
/**
 * Settings UI page adapter.
 *
 * @package WooSettingsUIDemo
 */

declare( strict_types=1 );

defined( 'ABSPATH' ) || exit;

/**
 * Adds demo shell metadata and scripts to the legacy Settings UI adapter.
 */
class WSUID_Settings_Page_Adapter extends \Automattic\WooCommerce\Admin\Settings\LegacySettingsPageAdapter {

	/**
	 * Build the Settings UI schema for the current section.
	 *
	 * @param string $section Section id. Empty string means the default section.
	 * @return array<mixed>
	 */
	public function get_schema( string $section ): array {
		$schema = parent::get_schema( $section );

		$schema = $this->normalize_channel_picker_schema( $schema );

		$schema['shell']['subtitle'] = __( 'Toggle WooCommerce Settings UI rendering and compare native fields across both renderers.', 'woo-settings-ui-demo' );
		$schema['shell']['badges']   = array(
			array(
				'label'  => __( 'Settings UI active', 'woo-settings-ui-demo' ),
				'intent' => 'success',
			),
		);

		return $schema;
	}

	/**
	 * Convert the legacy custom field type into the canonical Settings UI array type.
	 *
	 * @param array<mixed> $schema Settings UI schema.
	 * @return array<mixed>
	 */
	private function normalize_channel_picker_schema( array $schema ): array {
		if ( ! isset( $schema['groups'] ) || ! is_array( $schema['groups'] ) ) {
			return $schema;
		}

		foreach ( $schema['groups'] as &$group ) {
			if ( ! isset( $group['fields'] ) || ! is_array( $group['fields'] ) ) {
				continue;
			}

			foreach ( $group['fields'] as &$field ) {
				if ( ! is_array( $field ) || 'wsuid_notification_channels' !== ( $field['id'] ?? '' ) ) {
					continue;
				}

				$field['type']      = 'array';
				$field['component'] = 'woo-settings-ui-demo/channel-picker';
				$field['value']     = isset( $field['value'] ) && is_array( $field['value'] )
					? array_values( $field['value'] )
					: array();
			}
		}

		unset( $group, $field );

		return $schema;
	}

	/**
	 * Load the component registration script before the Settings UI app mounts.
	 *
	 * @param string $section Section id. Empty string means the default section.
	 * @return string[]
	 */
	public function get_script_handles( string $section ): array {
		return array( WSUID_SCRIPT_HANDLE );
	}
}
