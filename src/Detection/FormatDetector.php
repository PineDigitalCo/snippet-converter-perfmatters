<?php
/**
 * Format detector.
 *
 * @package SnippetConverterForPerfmatters
 */

declare(strict_types=1);

namespace SnippetConverterForPerfmatters\Detection;

use SnippetConverterForPerfmatters\Adapters\AdapterInterface;
use SnippetConverterForPerfmatters\Adapters\CodeSnippetsAdapter;
use SnippetConverterForPerfmatters\Adapters\GenericArrayAdapter;
use SnippetConverterForPerfmatters\Adapters\GenericKeyedAdapter;
use SnippetConverterForPerfmatters\Adapters\PerfmattersNativeAdapter;
use SnippetConverterForPerfmatters\Adapters\WpCodeAdapter;

/**
 * Selects the first matching adapter for decoded JSON.
 */
final class FormatDetector {

	/** @var list<AdapterInterface> */
	private array $adapters;

	public function __construct() {
		$this->adapters = [
			new PerfmattersNativeAdapter(),
			new WpCodeAdapter(),
			new CodeSnippetsAdapter(),
			new GenericArrayAdapter(),
			new GenericKeyedAdapter(),
		];
	}

	/**
	 * Parse JSON string into a detection result.
	 */
	public function detect_from_json( string $json ): DetectionResult {
		$data = json_decode( $json, true );

		if ( json_last_error() !== JSON_ERROR_NONE ) {
			return new DetectionResult(
				'',
				'',
				[],
				[ __( 'Invalid JSON file.', 'snippet-converter-for-perfmatters' ) ]
			);
		}

		return $this->detect( $data );
	}

	/**
	 * @param mixed $data Decoded JSON root.
	 */
	public function detect( mixed $data ): DetectionResult {
		foreach ( $this->adapters as $adapter ) {
			if ( ! $adapter->supports( $data ) ) {
				continue;
			}

			$snippets = $adapter->parse( $data );

			if ( $snippets !== [] ) {
				return new DetectionResult(
					$adapter->get_id(),
					$adapter->get_label(),
					$snippets
				);
			}
		}

		return new DetectionResult(
			'',
			'',
			[],
			[ __( 'No supported snippet format detected in this file.', 'snippet-converter-for-perfmatters' ) ]
		);
	}
}
