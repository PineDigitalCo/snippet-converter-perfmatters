<?php

declare(strict_types=1);

namespace SnippetConverterForPerfmatters\Tests;

use PHPUnit\Framework\TestCase;
use SnippetConverterForPerfmatters\Detection\FormatDetector;
use SnippetConverterForPerfmatters\Export\PerfmattersExporter;
use SnippetConverterForPerfmatters\Support\CodeNormalizer;

final class ConversionTest extends TestCase {

	public function test_code_snippets_fixture_is_detected_and_exported(): void {
		$result = $this->convert_fixture( 'code-snippets.json' );

		$this->assertSame( 'code_snippets', $result['adapter_id'] );
		$this->assertCount( 2, $result['snippets'] );
		$this->assertPmcsShape( $result['export'] );
		$first = reset( $result['export'] );
		$this->assertIsArray( $first );
		$this->assertStringStartsWith( "<?php\n", $first['code'] );
		$this->assertStringContainsString( "echo 'hello';", $first['code'] );
	}

	public function test_wpcode_fixture_is_detected_and_exported(): void {
		$result = $this->convert_fixture( 'wpcode.json' );

		$this->assertSame( 'wpcode', $result['adapter_id'] );
		$this->assertCount( 2, $result['snippets'] );
		$this->assertSame( 'js', $result['snippets'][0]->type );
		$this->assertPmcsShape( $result['export'] );
	}

	public function test_perfmatters_native_fixture_is_detected(): void {
		$result = $this->convert_fixture( 'perfmatters-native.json' );

		$this->assertSame( 'perfmatters_native', $result['adapter_id'] );
		$this->assertCount( 1, $result['snippets'] );
		$this->assertPmcsShape( $result['export'] );
	}

	public function test_generic_array_fixture_is_detected(): void {
		$result = $this->convert_fixture( 'generic-array.json' );

		$this->assertSame( 'generic_array', $result['adapter_id'] );
		$this->assertCount( 2, $result['snippets'] );
		$this->assertPmcsShape( $result['export'] );
	}

	public function test_strip_php_tags_removes_opening_tag(): void {
		$code = CodeNormalizer::strip_php_tags( "<?php\necho 'x';" );
		$this->assertSame( "echo 'x';", $code );
	}

	public function test_wrap_php_for_pmcs_import_matches_perfmatters_save_format(): void {
		$wrapped = CodeNormalizer::wrap_php_for_pmcs_import( "<?php\nadd_filter( 'init', '__return_true' );" );
		$this->assertSame( "<?php\nadd_filter( 'init', '__return_true' );", $wrapped );
	}

	/**
	 * @param array<string, array{meta: array<string, mixed>, code: string}> $export
	 */
	private function assertPmcsShape( array $export ): void {
		$this->assertNotEmpty( $export );

		foreach ( $export as $file_name => $snippet ) {
			$this->assertStringEndsWith( '.php', $file_name );
			$this->assertArrayHasKey( 'meta', $snippet );
			$this->assertArrayHasKey( 'code', $snippet );
			$this->assertIsString( $snippet['code'] );
			$this->assertNotSame( '', $snippet['code'] );
			$this->assertArrayHasKey( 'name', $snippet['meta'] );
			$this->assertArrayHasKey( 'type', $snippet['meta'] );
			$this->assertContains( $snippet['meta']['type'], [ 'php', 'js', 'css', 'html' ] );
			$this->assertSame( 0, $snippet['meta']['active'] );

			if ( $snippet['meta']['type'] === 'php' ) {
				$this->assertStringStartsWith( "<?php\n", $snippet['code'] );
			}
		}
	}

	/**
	 * @return array{
	 *   adapter_id: string,
	 *   snippets: list<\SnippetConverterForPerfmatters\Model\NormalizedSnippet>,
	 *   export: array<string, array{meta: array<string, mixed>, code: string}>
	 * }
	 */
	private function convert_fixture( string $fixture ): array {
		$path = __DIR__ . '/fixtures/' . $fixture;
		$json = file_get_contents( $path );
		$this->assertIsString( $json );

		$detector = new FormatDetector();
		$result   = $detector->detect_from_json( $json );
		$this->assertTrue( $result->is_success(), implode( ', ', $result->errors ) );

		$exporter = new PerfmattersExporter();

		return [
			'adapter_id' => $result->adapter_id,
			'snippets'   => $result->snippets,
			'export'     => $exporter->export( $result->snippets ),
		];
	}
}
