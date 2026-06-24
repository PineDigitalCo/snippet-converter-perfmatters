<?php
/**
 * Code Snippets plugin JSON adapter.
 *
 * @package SnippetConverterForPerfmatters
 */

declare(strict_types=1);

namespace SnippetConverterForPerfmatters\Adapters;

/**
 * Parses Code Snippets (flynsarmy) export files.
 */
final class CodeSnippetsAdapter extends AbstractAdapter {

	public function get_id(): string {
		return 'code_snippets';
	}

	public function get_label(): string {
		return __( 'Code Snippets', 'snippet-converter-for-perfmatters' );
	}

	public function supports( mixed $data ): bool {
		$rows = $this->extract_rows( $data );
		if ( $rows === [] ) {
			return false;
		}

		$first = $rows[0];
		return is_array( $first ) && isset( $first['code'] ) && ( isset( $first['name'] ) || isset( $first['desc'] ) );
	}

	public function parse( mixed $data ): array {
		$rows     = $this->extract_rows( $data );
		$snippets = [];

		foreach ( $rows as $item ) {
			if ( ! is_array( $item ) ) {
				continue;
			}

			$name = $this->first_string( $item, [ 'name', 'title' ] );
			if ( $name === '' && isset( $item['desc'] ) && is_string( $item['desc'] ) ) {
				$name = sanitize_text_field( wp_strip_all_tags( $item['desc'] ) );
			}

			$normalized = $this->make_snippet(
				$name,
				$item['code'] ?? '',
				isset( $item['scope'] ) && is_string( $item['scope'] ) ? $item['scope'] : ( isset( $item['type'] ) && is_string( $item['type'] ) ? $item['type'] : null )
			);

			if ( $normalized !== null ) {
				$snippets[] = $normalized;
			}
		}

		return $snippets;
	}

	/**
	 * @return list<mixed>
	 */
	private function extract_rows( mixed $data ): array {
		if ( ! is_array( $data ) ) {
			return [];
		}

		if ( isset( $data['generator'] ) && is_string( $data['generator'] ) && stripos( $data['generator'], 'code snippets' ) !== false && isset( $data['snippets'] ) && is_array( $data['snippets'] ) ) {
			return array_values( $data['snippets'] );
		}

		if ( array_is_list( $data ) ) {
			return $data;
		}

		return [];
	}
}
