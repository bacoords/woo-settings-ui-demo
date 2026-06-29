# Woo Settings UI Demo

Tutorial plugin for experimenting with the WooCommerce Settings UI renderer.

This repository shows how to:

- Add a custom WooCommerce settings tab.
- Add a section to an existing WooCommerce settings page.
- Toggle the experimental Settings UI renderer from inside that tab.
- Reuse the same WooCommerce settings array and save flow in both renderers.
- Add a custom field in the legacy PHP settings screen.
- Render that same saved option with a custom React component in Settings UI.
- Build JavaScript with `@wordpress/scripts` and WooCommerce dependency extraction.

The companion WooCommerce developer documentation is:

https://developer.woocommerce.com/docs/extensions/settings-and-config/settings-ui/

## Requirements

- WordPress with WooCommerce active.
- WooCommerce 10.9 or newer.
- PHP 7.4 or newer.
- Node 24.15.0, as defined in `.nvmrc`.
- Composer for PHP linting dependencies.

The plugin header includes `Requires Plugins: woocommerce`, so WordPress handles the WooCommerce dependency notice.

## Install

Place this directory in:

```text
wp-content/plugins/woo-settings-ui-demo
```

Then install dependencies:

```bash
nvm use
npm install
composer install
```

Build the JavaScript and CSS:

```bash
npm run build
```

Activate the plugin in WordPress, then open:

```text
WooCommerce > Settings > Settings UI Demo
```

The plugin also registers an example section under:

```text
WooCommerce > Settings > Products > Settings UI Demo
```

## Try The Demo

1. Open the `Settings UI Demo` settings tab.
2. The page starts in the legacy WooCommerce settings renderer.
3. Enable `Enable Settings UI renderer`.
4. Save changes.
5. Reload the settings tab.

When the option is enabled, the plugin adds `settings-ui` through the `woocommerce_admin_features` filter. WooCommerce then renders this opted-in page through Settings UI. Disable the same option and save again to return to the legacy renderer.

To compare the registered-section API, open `WooCommerce > Settings > Products > Settings UI Demo`. That section is added to WooCommerce's existing Products settings page rather than creating another top-level settings tab.

## Tutorial Walkthrough

### 1. Bootstrap The Plugin

[woo-settings-ui-demo.php](woo-settings-ui-demo.php) keeps the top-level WordPress hooks:

- `woocommerce_admin_features` conditionally enables the `settings-ui` feature.
- `woocommerce_get_settings_pages` adds the demo settings page.
- `admin_enqueue_scripts` registers the built JS and CSS assets.

The main file intentionally stays small so the settings-page behavior lives with the settings page class.

### 2. Create A WooCommerce Settings Page

[includes/class-wsuid-settings-page.php](includes/class-wsuid-settings-page.php) extends `WC_Settings_Page`.

The page defines two sections in one settings array:

- `wsuid_demo_options`: the renderer toggle.
- `wsuid_demo_fields`: example settings fields.

The same settings array feeds both renderers. That keeps option IDs, defaults, and the standard WooCommerce save path consistent.

### 3. Register A Section Under Products

[includes/class-wsuid-products-section.php](includes/class-wsuid-products-section.php) extends WooCommerce's `SettingsSection` base class.

The section declares:

- Parent page ID: `products`.
- Section ID: `settings_ui_demo`.
- Label: `Settings UI Demo`.
- Settings fields returned from `get_settings()`.

The plugin registers that section through:

```php
add_action( 'woocommerce_settings_sections_registration', 'wsuid_register_products_settings_section' );
```

This is the path to use when an extension should add a section to an existing WooCommerce settings page instead of owning a full tab. WooCommerce includes registered sections in the legacy section navigation and adapts them for Settings UI when the feature is enabled.

### 4. Opt Into Settings UI

The settings page implements:

```php
public function get_settings_ui_page(): ?\Automattic\WooCommerce\Admin\Settings\SettingsUIPageInterface {
	return new WSUID_Settings_Page_Adapter( $this );
}
```

Returning a `SettingsUIPageInterface` opts the page into the Settings UI renderer when WooCommerce's `settings-ui` feature is enabled.

### 5. Adapt The Legacy Settings Array

[includes/class-wsuid-settings-page-adapter.php](includes/class-wsuid-settings-page-adapter.php) extends WooCommerce's `LegacySettingsPageAdapter`.

It customizes the generated schema by:

- Adding shell metadata such as subtitle and badges.
- Returning the custom component script handle.
- Translating the legacy custom field type `wsuid_channel_picker` into the canonical Settings UI `array` type.

That last step matters because the legacy renderer uses PHP field types, while Settings UI needs a canonical field type and a React component name.

### 6. Add A Custom Field In Both Renderers

The notification channel setting uses one option ID:

```php
'id'        => 'wsuid_notification_channels',
'type'      => 'wsuid_channel_picker',
'component' => 'woo-settings-ui-demo/channel-picker',
```

In the legacy renderer, WooCommerce sees the custom `type` and fires:

```php
do_action( 'woocommerce_admin_field_wsuid_channel_picker', $value );
```

`WSUID_Settings_Page::output_channel_picker_field()` renders the custom PHP field.

In Settings UI, the adapter keeps the same option ID but changes the schema field to:

```php
'type'      => 'array',
'component' => 'woo-settings-ui-demo/channel-picker',
```

The React component is registered in [src/index.js](src/index.js), and implemented in [src/channel-picker.js](src/channel-picker.js).

### 7. Share Sanitization

Both renderers save `wsuid_notification_channels` through WooCommerce settings saving.

The page registers an option-specific sanitizer:

```php
add_filter(
	'woocommerce_admin_settings_sanitize_option_wsuid_notification_channels',
	array( $this, 'sanitize_channel_picker_option' ),
	10,
	3
);
```

That sanitizer whitelists submitted channel values against the field's `options` array. This keeps the custom PHP field and the custom React field saving the same clean array.

## JavaScript Build

Source files live directly under `src/`:

- [src/index.js](src/index.js) registers the Settings UI extension.
- [src/channel-picker.js](src/channel-picker.js) exports the custom React field.
- [src/style.css](src/style.css) styles the picker in both renderers.

The generated assets live in `build/` and are committed so the plugin can run without a local build step.

Useful commands:

```bash
npm run build
npm run start
npm run lint
```

The webpack config uses `@woocommerce/dependency-extraction-webpack-plugin` so WooCommerce and WordPress packages are externalized to registered script dependencies.

## PHP Linting

Install Composer dependencies, then run:

```bash
composer lint
```

The PHPCS config is in [phpcs.xml.dist](phpcs.xml.dist). It uses WordPress coding standards and enforces the `woo-settings-ui-demo` text domain.

## File Map

```text
woo-settings-ui-demo.php
  Plugin bootstrap, constants, feature toggle, settings page registration.

includes/class-wsuid-settings-page.php
  WC_Settings_Page implementation, settings array, legacy custom field renderer, sanitizer.

includes/class-wsuid-settings-page-adapter.php
  Settings UI schema adapter and custom component script handle.

includes/class-wsuid-products-section.php
  Registered section under WooCommerce's existing Products settings page.

includes/class-wsuid-assets.php
  Registers built JS/CSS and the local Settings UI SDK compatibility alias.

src/index.js
  Registers the Settings UI component map.

src/channel-picker.js
  React component for the Settings UI channel picker.

src/style.css
  Shared channel picker styles for legacy and Settings UI renderers.
```

## Verification Checklist

After making changes, run:

```bash
nvm use
npm run build
npm run lint
composer lint
php -l woo-settings-ui-demo.php
php -l includes/class-wsuid-settings-page.php
php -l includes/class-wsuid-settings-page-adapter.php
php -l includes/class-wsuid-products-section.php
php -l includes/class-wsuid-assets.php
```

For runtime checks, verify both renderers:

- With Settings UI disabled, the page uses the legacy WooCommerce settings table.
- With Settings UI enabled, the page renders through the React Settings UI shell.
- The notification channel picker saves the same `wsuid_notification_channels` option in both modes.
- `WooCommerce > Settings > Products > Settings UI Demo` appears as a registered section under Products.
