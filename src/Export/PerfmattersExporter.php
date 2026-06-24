<?php
/**
 * Perfmatters PMCS exporter.
 *
 * @package SnippetConverterForPerfmatters
 */

declare(strict_types=1);

namespace SnippetConverterForPerfmatters\Export;

use SnippetConverterForPerfmatters\Model\NormalizedSnippet;

/**
 * Builds Perfmatters-native import JSON.
 */
final class PerfmattersExporter {

	/**
	 * @param list<NormalizedSnippet> $snippets Normalized snippets.
	 * @return array<string, array{meta: array<string, mixed>, code: string}>
	 */
	public function export( array $snippets ): array {
		$output = [];
		$index  = 1;

		foreach ( $snippets as $snippet ) {
			$slug      = sanitize_title( $snippet->name );
			$slug      = $slug !== '' ? $slug : 'snippet';
			$file_name = $this->unique_file_name( $output, $index, $slug );

			$output[ $file_name ] = [
				'meta' => $this->default_meta( $snippet ),
				'code' => $snippet->export_code(),
			];

			++$index;
		}

		return $output;
	}

	/**
	 * @param array<string, array{meta: array<string, mixed>, code: string}> $output Current export.
	 */
	private function unique_file_name( array $output, int $index, string $slug ): string {
		$candidate = $index . '-' . $slug . '.php';

		while ( isset( $output[ $candidate ] ) ) {
			++$index;
			$candidate = $index . '-' . $slug . '.php';
		}

		return $candidate;
	}

	/**
	 * @return array<string, mixed>
	 */
	private function default_meta( NormalizedSnippet $snippet ): array {
		return [
			'name'          => $snippet->name,
			'type'          => $snippet->type,
			'description'   => '',
			'active'        => 0,
			'priority'      => 10,
			'location'      => '',
			'optimizations' => [],
			'conditions'    => [],
			'tags'          => [],
		];
	}
}
