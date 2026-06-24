<?php
/**
 * Generic array JSON adapter.
 *
 * @package SnippetConverterForPerfmatters
 */

declare(strict_types=1);

namespace SnippetConverterForPerfmatters\Adapters;

/**
 * Fallback parser for list-shaped JSON exports.
 */
final class GenericArrayAdapter extends AbstractAdapter {

	public function get_id(): string {
		return 'generic_array';
	}

	public function get_label(): string {
		return __( 'Generic snippet list', 'snippet-converter-for-perfmatters' );
	}

	public function supports( mixed $data ): bool {
		if ( ! is_array( $data ) || $data === [] || ! array_is_list( $data ) ) {
			return false;
		}

		foreach ( $data as $item ) {
			if ( ! is_array( $item ) ) {
				return false;
			}

			$name = $this->first_string( $item, [ 'name', 'title', 'label', 'snippet_name' ] );
			$code = $this->first_string( $item, [ 'code', 'content', 'snippet', 'body', 'value' ] );

			if ( $name !== '' && $code !== '' ) {
				return true;
			}
		}

		return false;
	}

	public function parse( mixed $data ): array {
		if ( ! is_array( $data ) || ! array_is_list( $data ) ) {
			return [];
		}

		$snippets = [];

		foreach ( $data as $item ) {
			if ( ! is_array( $item ) ) {
				continue;
			}

			$normalized = $this->snippet_from_row(
				$item,
				[ 'name', 'title', 'label', 'snippet_name' ],
				[ 'code', 'content', 'snippet', 'body', 'value' ],
				[ 'type', 'code_type', 'scope', 'language' ]
			);

			if ( $normalized !== null ) {
				$snippets[] = $normalized;
			}
		}

		return $snippets;
	}
}
