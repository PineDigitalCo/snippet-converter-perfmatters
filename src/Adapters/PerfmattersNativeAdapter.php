<?php
/**
 * Perfmatters native JSON adapter.
 *
 * @package SnippetConverterForPerfmatters
 */

declare(strict_types=1);

namespace SnippetConverterForPerfmatters\Adapters;

use SnippetConverterForPerfmatters\Model\NormalizedSnippet;

/**
 * Parses Perfmatters PMCS export files.
 */
final class PerfmattersNativeAdapter extends AbstractAdapter {

	public function get_id(): string {
		return 'perfmatters_native';
	}

	public function get_label(): string {
		return __( 'Perfmatters PMCS', 'snippet-converter-for-perfmatters' );
	}

	public function supports( mixed $data ): bool {
		if ( ! is_array( $data ) || $data === [] ) {
			return false;
		}

		foreach ( $data as $file_name => $snippet ) {
			if ( ! is_string( $file_name ) || ! is_array( $snippet ) ) {
				continue;
			}

			if ( ! str_ends_with( strtolower( $file_name ), '.php' ) ) {
				continue;
			}

			if ( empty( $snippet['meta'] ) || ! is_array( $snippet['meta'] ) || ! isset( $snippet['code'] ) || ! is_string( $snippet['code'] ) ) {
				continue;
			}

			return true;
		}

		return false;
	}

	public function parse( mixed $data ): array {
		if ( ! is_array( $data ) ) {
			return [];
		}

		$snippets = [];

		foreach ( $data as $snippet ) {
			if ( ! is_array( $snippet ) ) {
				continue;
			}

			$meta = $snippet['meta'] ?? [];
			if ( ! is_array( $meta ) ) {
				continue;
			}

			$normalized = $this->make_snippet(
				$meta['name'] ?? '',
				$snippet['code'] ?? '',
				isset( $meta['type'] ) && is_string( $meta['type'] ) ? $meta['type'] : null
			);

			if ( $normalized instanceof NormalizedSnippet ) {
				$snippets[] = $normalized;
			}
		}

		return $snippets;
	}
}
