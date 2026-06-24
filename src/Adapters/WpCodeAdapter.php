<?php
/**
 * WPCode JSON adapter.
 *
 * @package SnippetConverterForPerfmatters
 */

declare(strict_types=1);

namespace SnippetConverterForPerfmatters\Adapters;

/**
 * Parses WPCode export files.
 */
final class WpCodeAdapter extends AbstractAdapter {

	public function get_id(): string {
		return 'wpcode';
	}

	public function get_label(): string {
		return __( 'WPCode', 'snippet-converter-for-perfmatters' );
	}

	public function supports( mixed $data ): bool {
		if ( ! is_array( $data ) ) {
			return false;
		}

		if ( isset( $data['generator'] ) && is_string( $data['generator'] ) && stripos( $data['generator'], 'wpcode' ) !== false ) {
			return isset( $data['snippets'] ) && is_array( $data['snippets'] );
		}

		if ( isset( $data['snippets'] ) && is_array( $data['snippets'] ) && $data['snippets'] !== [] ) {
			$first = reset( $data['snippets'] );
			return is_array( $first ) && ( isset( $first['code_type'] ) || isset( $first['title'] ) );
		}

		return false;
	}

	public function parse( mixed $data ): array {
		if ( ! is_array( $data ) || ! isset( $data['snippets'] ) || ! is_array( $data['snippets'] ) ) {
			return [];
		}

		$snippets = [];

		foreach ( $data['snippets'] as $item ) {
			if ( ! is_array( $item ) ) {
				continue;
			}

			$normalized = $this->snippet_from_row(
				$item,
				[ 'title', 'name', 'snippet_title' ],
				[ 'code', 'snippet_code', 'content' ],
				[ 'code_type', 'type' ]
			);

			if ( $normalized !== null ) {
				$snippets[] = $normalized;
			}
		}

		return $snippets;
	}
}
