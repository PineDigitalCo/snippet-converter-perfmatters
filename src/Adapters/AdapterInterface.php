<?php
/**
 * Adapter contract.
 *
 * @package SnippetConverterForPerfmatters
 */

declare(strict_types=1);

namespace SnippetConverterForPerfmatters\Adapters;

use SnippetConverterForPerfmatters\Model\NormalizedSnippet;

/**
 * Converts a known JSON shape into normalized snippets.
 */
interface AdapterInterface {

	/**
	 * Machine-readable adapter identifier.
	 */
	public function get_id(): string;

	/**
	 * Human-readable adapter label.
	 */
	public function get_label(): string;

	/**
	 * Whether this adapter can parse the given decoded JSON root.
	 *
	 * @param mixed $data Decoded JSON.
	 */
	public function supports( mixed $data ): bool;

	/**
	 * Parse snippets from decoded JSON.
	 *
	 * @param mixed $data Decoded JSON.
	 * @return list<NormalizedSnippet>
	 */
	public function parse( mixed $data ): array;
}
