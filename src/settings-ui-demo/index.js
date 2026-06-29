import { registerSettingsExtension } from '@woocommerce/settings-ui';

import { ChannelPicker } from './channel-picker';
import './style.css';

registerSettingsExtension( {
	scope: {
		page: 'settings_ui_demo',
	},
	components: {
		'woo-settings-ui-demo/channel-picker': ChannelPicker,
	},
} );
