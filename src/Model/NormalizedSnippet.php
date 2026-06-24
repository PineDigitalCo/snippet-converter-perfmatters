<?php
/**
 * Normalized snippet model.
 *
 * @package SnippetConverterForPerfmatters
 */

declare(strict_types=1);

namespace SnippetConverterForPerfmatters\Model;

use SnippetConverterForPerfmatters\Support\CodeNormalizer;

/**
 * Canonical snippet representation used between adapters and the exporter.
 */
final class NormalizedSnippet {

	/** @var list<string> */
	public array $warnings = [];

	/**
	 * @param string               $name Snippet display name.
	 * @param string               $type One of php, js, css, html.
	 * @param string               $code Raw snippet code.
	 * @param list<string>         $warnings Optional conversion warnings.
	 */
	public function __construct(
		public string $name,
		public string $type,
		public string $code,
		array $warnings = []
	) {
		$this->warnings = $warnings;
	}

	/**
	 * Code prepared for Perfmatters PMCS import.
	 *
	 * PHP snippets must start with an opening tag in the stored file. Perfmatters
	 * adds this on manual save but not on JSON import, so we match save() here.
	 */
	public function export_code(): string {
		if ( $this->type === 'php' ) {
			return CodeNormalizer::wrap_php_for_pmcs_import( $this->code );
		}

		return $this->code;
	}
}
