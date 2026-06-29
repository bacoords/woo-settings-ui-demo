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

		add_action( 'woocommerce_admin_field_wsuid_channel_picker', array( $this, 'output_channel_picker_field' ) );
		add_filter( 'woocommerce_admin_settings_sanitize_option_wsuid_notification_channels', array( $this, 'sanitize_channel_picker_option' ), 10, 3 );
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
	 * Render the custom channel picker field in the legacy settings table.
	 *
	 * @param array<mixed> $value Field definition.
	 */
	public function output_channel_picker_field( array $value ): void {
		$field_description = WC_Admin_Settings::get_field_description( $value );
		$selected_values   = $this->sanitize_channel_picker_option( array(), $value, $value['value'] ?? array() );
		$field_id          = isset( $value['id'] ) ? (string) $value['id'] : '';
		$field_name        = isset( $value['field_name'] ) ? (string) $value['field_name'] : $field_id;
		$field_title       = isset( $value['title'] ) ? (string) $value['title'] : '';
		$field_options     = isset( $value['options'] ) && is_array( $value['options'] ) ? $value['options'] : array();
		$row_class         = isset( $value['row_class'] ) ? (string) $value['row_class'] : '';
		$description       = isset( $field_description['description'] ) ? (string) $field_description['description'] : '';
		$tooltip_html      = isset( $field_description['tooltip_html'] ) ? (string) $field_description['tooltip_html'] : '';
		$first_option_id   = '';

		if ( array() !== $field_options ) {
			$first_option_id = $field_id . '_' . sanitize_key( (string) array_key_first( $field_options ) );
		}
		?>
		<tr class="<?php echo esc_attr( $row_class ); ?>">
			<th scope="row" class="titledesc">
				<label for="<?php echo esc_attr( $first_option_id ? $first_option_id : $field_id ); ?>">
					<?php echo esc_html( $field_title ); ?> <?php echo $tooltip_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WooCommerce formats setting tooltip HTML. ?>
				</label>
			</th>
			<td class="forminp forminp-wsuid-channel-picker">
				<fieldset>
					<legend class="screen-reader-text"><span><?php echo esc_html( $field_title ); ?></span></legend>
					<div class="wsuid-channel-picker wsuid-channel-picker--legacy" role="group" aria-label="<?php echo esc_attr( $field_title ); ?>">
						<?php
						foreach ( $field_options as $option_value => $option_label ) {
							$option_value = (string) $option_value;
							$option_id    = $field_id . '_' . sanitize_key( $option_value );
							$is_selected  = in_array( $option_value, $selected_values, true );
							?>
							<label class="wsuid-channel-picker__option<?php echo $is_selected ? ' is-selected' : ''; ?>" for="<?php echo esc_attr( $option_id ); ?>">
								<input
									id="<?php echo esc_attr( $option_id ); ?>"
									name="<?php echo esc_attr( $field_name ); ?>[]"
									type="checkbox"
									value="<?php echo esc_attr( $option_value ); ?>"
									class="wsuid-channel-picker__input"
									<?php checked( $is_selected ); ?>
								/>
								<span class="wsuid-channel-picker__indicator" aria-hidden="true"></span>
								<span class="wsuid-channel-picker__label"><?php echo esc_html( (string) $option_label ); ?></span>
							</label>
							<?php
						}
						?>
					</div>
					<?php echo wp_kses_post( $description ); ?>
				</fieldset>
			</td>
		</tr>
		<?php
	}

	/**
	 * Sanitize selected notification channels for both renderers.
	 *
	 * @param mixed        $value     Prepared value from WooCommerce.
	 * @param array<mixed> $option    Field definition.
	 * @param mixed        $raw_value Raw submitted value.
	 * @return string[]
	 */
	public function sanitize_channel_picker_option( $value, array $option, $raw_value ): array {
		$allowed_values = isset( $option['options'] ) && is_array( $option['options'] )
			? array_map( 'strval', array_keys( $option['options'] ) )
			: array();
		$posted_values  = array();
		$clean_values   = array();

		foreach ( (array) $raw_value as $posted_value ) {
			if ( is_scalar( $posted_value ) ) {
				$posted_values[] = (string) wc_clean( $posted_value );
			}
		}

		foreach ( $allowed_values as $allowed_value ) {
			if ( in_array( $allowed_value, $posted_values, true ) ) {
				$clean_values[] = $allowed_value;
			}
		}

		return $clean_values;
	}

	/**
	 * Get settings for the default section used by both renderers.
	 *
	 * @return array<mixed>
	 */
	protected function get_settings_for_default_section() {
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
				'desc'      => __( 'Legacy mode renders this as a custom PHP field. Settings UI mode renders the same saved option with a React component.', 'woo-settings-ui-demo' ),
				'id'        => 'wsuid_notification_channels',
				'type'      => 'wsuid_channel_picker',
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
}
