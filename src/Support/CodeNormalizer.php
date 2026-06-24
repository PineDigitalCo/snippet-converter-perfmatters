<?php
/**
 * Code normalization helpers.
 *
 * @package SnippetConverterForPerfmatters
 */

declare(strict_types=1);

namespace SnippetConverterForPerfmatters\Support;

/**
 * Shared code and type utilities.
 */
final class CodeNormalizer {

	/** @var list<string> */
	private const VALID_TYPES = [ 'php', 'js', 'css', 'html' ];

	/**
	 * Remove opening PHP tags for Perfmatters compatibility.
	 */
	public static function strip_php_tags( string $code ): string {
		$code = trim( $code );

		if ( preg_match( '/^<\?php\s*/i', $code ) ) {
			$code = (string) preg_replace( '/^<\?php\s*/i', '', $code );
		}

		$code = (string) preg_replace( '/\?>\s*$/', '', $code );

		return trim( $code );
	}

	/**
	 * Format PHP snippet code the way Perfmatters stores it on disk after save().
	 */
	public static function wrap_php_for_pmcs_import( string $code ): string {
		$code = self::strip_php_tags( $code );

		return '<?php' . PHP_EOL . rtrim( $code, '?>' );
	}

	/**
	 * Map a source-specific type hint to a PMCS type.
	 */
	public static function normalize_type( ?string $explicit, string $code ): string {
		if ( is_string( $explicit ) && $explicit !== '' ) {
			$mapped = self::map_explicit_type( $explicit );
			if ( $mapped !== null ) {
				return $mapped;
			}
		}

		return self::infer_type_from_code( $code );
	}

	/**
	 * @return 'php'|'js'|'css'|'html'
	 */
	public static function infer_type_from_code( string $code ): string {
		$trimmed = ltrim( $code );

		if ( preg_match( '/^<\?php\b/i', $trimmed ) ) {
			return 'php';
		}

		if ( preg_match( '/^<style\b/i', $trimmed ) ) {
			return 'css';
		}

		if ( preg_match( '/^<script\b/i', $trimmed ) ) {
			return 'js';
		}

		if ( preg_match( '/^<[!a-z]/i', $trimmed ) ) {
			return 'html';
		}

		return 'php';
	}

	/**
	 * @return 'php'|'js'|'css'|'html'|null
	 */
	private static function map_explicit_type( string $explicit ): ?string {
		$key = strtolower( trim( $explicit ) );

		$aliases = [
			'php'           => 'php',
			'php-snippet'   => 'php',
			'js'            => 'js',
			'javascript'    => 'js',
			'css'           => 'css',
			'html'          => 'html',
			'html-snippet'  => 'html',
			'universal'     => 'html',
			'text'          => 'html',
			'global'        => 'php',
			'admin'         => 'php',
			'front-end'     => 'php',
			'frontend'      => 'php',
			'single-use'    => 'php',
			'content'       => 'php',
		];

		if ( isset( $aliases[ $key ] ) ) {
			return $aliases[ $key ];
		}

		if ( in_array( $key, self::VALID_TYPES, true ) ) {
			return $key;
		}

		return null;
	}

	/**
	 * @param mixed $value Candidate snippet name.
	 */
	public static function sanitize_name( mixed $value ): string {
		if ( ! is_string( $value ) ) {
			return '';
		}

		$name = sanitize_text_field( wp_strip_all_tags( $value ) );

		return trim( $name );
	}

	/**
	 * @param mixed $value Candidate snippet code.
	 */
	public static function sanitize_code( mixed $value ): string {
		if ( ! is_string( $value ) ) {
			return '';
		}

		return $value;
	}
}
