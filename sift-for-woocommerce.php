<?php
/**
 * The Team51 Plugin Scaffold bootstrap file.
 *
 * @since       1.0.0
 * @version     1.0.0
 * @author      WordPress.com Special Projects
 * @license     GPL-3.0-or-later
 *
 * @noinspection    ALL
 *
 * @wordpress-plugin
 * Plugin Name:             Sift For WooCommerce
 * Plugin URI:              https://wpspecialprojects.wordpress.com
 * Description:             A plugin to integrate WooCommerce with Sift Science Fraud Detection
 * Version:                 0.0.1-alpha
 * Requires at least:       6.2
 * Tested up to:            6.2
 * Requires PHP:            8.0
 * Author:                  georgestephanis
 * Author URI:              https://wpspecialprojects.wordpress.com
 * License:                 GPL v3 or later
 * License URI:             https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:             sift-for-woocommerce
 * Domain Path:             /languages
 **/

defined( 'ABSPATH' ) || exit;

// Load plugin translations so they are available even for the error admin notices.
add_action(
	'init',
	static function () {
		load_plugin_textdomain(
			'sift-for-woocommerce',
			false,
			__DIR__ . '/languages'
		);
	}
);

// Load the autoloader.
if ( ! is_file( __DIR__ . '/vendor/autoload.php' ) ) {
	add_action(
		'admin_notices',
		static function () {
			$message      = __( 'It seems like <strong>Sift Decisions</strong> is corrupted. Please reinstall!', 'sift-for-woocommerce' );
			$html_message = wp_sprintf( '<div class="error notice wpcomsp-scaffold-error">%s</div>', wpautop( $message ) );
			echo wp_kses_post( $html_message );
		}
	);
	return;
}
require_once __DIR__ . '/vendor/autoload.php';

require_once __DIR__ . '/src/sift-for-woocommerce.php';

\Sift_For_WooCommerce\SiftDecisions::get_instance();
