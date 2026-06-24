<?php
/**
 * Adapter helpers.
 *
 * @package SnippetConverterForPerfmatters
 */

declare(strict_types=1);

namespace SnippetConverterForPerfmatters\Adapters;

use SnippetConverterForPerfmatters\Model\NormalizedSnippet;
use SnippetConverterForPerfmatters\Support\CodeNormalizer;

/**
 * Shared adapter utilities.
 */
abstract class AbstractAdapter implements AdapterInterface {

	/**
	 * Build a normalized snippet or return null when required fields are missing.
	 *
	 * @param mixed       $name_field Name value from source.
	 * @param mixed       $code_field Code value from source.
	 * @param string|null $type_field Optional explicit type.
	 * @param list<string> $warnings Optional warnings.
	 */
	protected function make_snippet(
		mixed $name_field,
		mixed $code_field,
		?string $type_field = null,
		array $warnings = []
	): ?NormalizedSnippet {
		$name = CodeNormalizer::sanitize_name( $name_field );
		$code = CodeNormalizer::sanitize_code( $code_field );

		if ( $name === '' || $code === '' ) {
			return null;
		}

		$type = CodeNormalizer::normalize_type( $type_field, $code );

		if ( $type === 'php' && preg_match( '/^<\?php\b/i', ltrim( $code ) ) ) {
			$warnings[] = __( 'Leading <?php will be removed for Perfmatters import.', 'snippet-converter-for-perfmatters' );
		}

		return new NormalizedSnippet( $name, $type, $code, $warnings );
	}

	/**
	 * @param array<string, mixed> $item Source snippet row.
	 * @param list<string>         $name_keys Candidate name keys.
	 * @param list<string>         $code_keys Candidate code keys.
	 * @param list<string>         $type_keys Candidate type keys.
	 */
	protected function snippet_from_row(
		array $item,
		array $name_keys,
		array $code_keys,
		array $type_keys = []
	): ?NormalizedSnippet {
		$name = $this->first_string( $item, $name_keys );
		$code = $this->first_string( $item, $code_keys );
		$type = $this->first_string( $item, $type_keys );

		return $this->make_snippet( $name, $code, $type !== '' ? $type : null );
	}

	/**
	 * @param array<string, mixed> $item Source row.
	 * @param list<string>         $keys Candidate keys.
	 */
	protected function first_string( array $item, array $keys ): string {
		foreach ( $keys as $key ) {
			if ( ! array_key_exists( $key, $item ) ) {
				continue;
			}

			$value = $item[ $key ];
			if ( is_string( $value ) && trim( $value ) !== '' ) {
				return $value;
			}
		}

		return '';
	}
}
