<?php
/**
 * Generic keyed JSON adapter.
 *
 * @package SnippetConverterForPerfmatters
 */

declare(strict_types=1);

namespace SnippetConverterForPerfmatters\Adapters;

use SnippetConverterForPerfmatters\Model\NormalizedSnippet;

/**
 * Fallback parser for map-shaped JSON exports.
 */
final class GenericKeyedAdapter extends AbstractAdapter {

	public function get_id(): string {
		return 'generic_keyed';
	}

	public function get_label(): string {
		return __( 'Generic keyed snippets', 'snippet-converter-for-perfmatters' );
	}

	public function supports( mixed $data ): bool {
		if ( ! is_array( $data ) || $data === [] || array_is_list( $data ) ) {
			return false;
		}

		foreach ( $data as $key => $snippet ) {
			if ( ! is_string( $key ) || ! is_array( $snippet ) ) {
				return false;
			}

			if ( isset( $snippet['code'] ) && is_string( $snippet['code'] ) ) {
				return true;
			}
		}

		return false;
	}

	public function parse( mixed $data ): array {
		if ( ! is_array( $data ) || array_is_list( $data ) ) {
			return [];
		}

		$snippets = [];

		foreach ( $data as $key => $snippet ) {
			if ( ! is_array( $snippet ) || ! isset( $snippet['code'] ) || ! is_string( $snippet['code'] ) ) {
				continue;
			}

			$name = '';
			$type = null;

			if ( isset( $snippet['meta'] ) && is_array( $snippet['meta'] ) ) {
				$name = $this->first_string( $snippet['meta'], [ 'name', 'title', 'label' ] );
				$type = $this->first_string( $snippet['meta'], [ 'type', 'code_type' ] );
			}

			if ( $name === '' ) {
				$name = $this->first_string( $snippet, [ 'name', 'title', 'label' ] );
			}

			if ( $name === '' && is_string( $key ) ) {
				$name = preg_replace( '/\.php$/i', '', $key ) ?? $key;
				$name = str_replace( [ '-', '_' ], ' ', $name );
			}

			if ( $type === '' ) {
				$type = $this->first_string( $snippet, [ 'type', 'code_type' ] );
			}

			$normalized = $this->make_snippet(
				$name,
				$snippet['code'],
				$type !== '' ? $type : null
			);

			if ( $normalized instanceof NormalizedSnippet ) {
				$snippets[] = $normalized;
			}
		}

		return $snippets;
	}
}
