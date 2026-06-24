<?php
/**
 * Format detection result.
 *
 * @package SnippetConverterForPerfmatters
 */

declare(strict_types=1);

namespace SnippetConverterForPerfmatters\Detection;

use SnippetConverterForPerfmatters\Model\NormalizedSnippet;

/**
 * Result of parsing an uploaded JSON file.
 */
final class DetectionResult {

	/**
	 * @param string                   $adapter_id   Matched adapter ID.
	 * @param string                   $adapter_label Matched adapter label.
	 * @param list<NormalizedSnippet>  $snippets     Parsed snippets.
	 * @param list<string>             $errors       Fatal parse errors.
	 */
	public function __construct(
		public string $adapter_id,
		public string $adapter_label,
		public array $snippets,
		public array $errors = []
	) {}

	public function is_success(): bool {
		return $this->errors === [] && $this->snippets !== [];
	}
}
