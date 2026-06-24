<?php
/**
 * Plugin bootstrap.
 *
 * @package SnippetConverterForPerfmatters
 */

declare(strict_types=1);

namespace SnippetConverterForPerfmatters;

use SnippetConverterForPerfmatters\Admin\ConverterPage;

/**
 * Main plugin class.
 */
final class Plugin {

	/**
	 * Initialize hooks.
	 */
	public static function init(): void {
		if ( is_admin() ) {
			ConverterPage::register();
		}
	}

	/**
	 * Whether Perfmatters is installed and active.
	 */
	public static function is_perfmatters_active(): bool {
		return defined( 'PERFMATTERS_VERSION' ) || class_exists( '\Perfmatters\PMCS\PMCS' );
	}
}
