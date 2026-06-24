<?php
/**
 * Plugin Name:       Snippet Converter for Perfmatters
 * Plugin URI:        https://github.com/PineDigitalCo/snippet-converter-perfmatters
 * Description:       Convert third-party code snippet JSON exports into Perfmatters PMCS import format.
 * Version:           1.0.0
 * Author:            Snippet Converter for Perfmatters
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       snippet-converter-for-perfmatters
 * Requires at least: 5.8
 * Requires PHP:      8.1
 *
 * @package SnippetConverterForPerfmatters
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'SCFP_VERSION' ) ) {
	define( 'SCFP_VERSION', '1.0.0' );
}
if ( ! defined( 'SCFP_FILE' ) ) {
	define( 'SCFP_FILE', __FILE__ );
}
if ( ! defined( 'SCFP_DIR' ) ) {
	define( 'SCFP_DIR', plugin_dir_path( __FILE__ ) );
}
if ( ! defined( 'SCFP_URL' ) ) {
	define( 'SCFP_URL', plugin_dir_url( __FILE__ ) );
}

$autoload = SCFP_DIR . 'vendor/autoload.php';
if ( is_readable( $autoload ) ) {
	require_once $autoload;
} else {
	spl_autoload_register(
		static function ( string $class_name ): void {
			if ( strpos( $class_name, 'SnippetConverterForPerfmatters\\' ) !== 0 ) {
				return;
			}

			$relative = substr( $class_name, strlen( 'SnippetConverterForPerfmatters\\' ) );
			$file     = SCFP_DIR . 'src/' . str_replace( '\\', '/', $relative ) . '.php';
			if ( is_readable( $file ) ) {
				require_once $file;
			}
		}
	);
}

add_action(
	'init',
	static function (): void {
		load_plugin_textdomain(
			'snippet-converter-for-perfmatters',
			false,
			dirname( plugin_basename( SCFP_FILE ) ) . '/languages'
		);
	},
	0
);

if ( class_exists( \SnippetConverterForPerfmatters\Plugin::class ) ) {
	\SnippetConverterForPerfmatters\Plugin::init();
}
