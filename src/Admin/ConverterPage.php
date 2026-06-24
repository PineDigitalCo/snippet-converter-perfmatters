<?php
/**
 * Admin converter page.
 *
 * @package SnippetConverterForPerfmatters
 */

declare(strict_types=1);

namespace SnippetConverterForPerfmatters\Admin;

use SnippetConverterForPerfmatters\Detection\DetectionResult;
use SnippetConverterForPerfmatters\Detection\FormatDetector;
use SnippetConverterForPerfmatters\Export\PerfmattersExporter;
use SnippetConverterForPerfmatters\Model\NormalizedSnippet;
use SnippetConverterForPerfmatters\Plugin;

/**
 * Tools screen for converting snippet JSON exports.
 */
final class ConverterPage {

	private const PAGE_SLUG       = 'snippet-converter-for-perfmatters';
	private const NONCE_ACTION    = 'scfp_converter';
	private const TRANSIENT_PREFIX = 'scfp_export_';

	/**
	 * Register admin hooks.
	 */
	public static function register(): void {
		add_action( 'admin_menu', [ self::class, 'register_menu' ] );
		add_action( 'admin_post_scfp_upload', [ self::class, 'handle_upload' ] );
		add_action( 'admin_post_scfp_download', [ self::class, 'handle_download' ] );
		add_action( 'admin_enqueue_scripts', [ self::class, 'enqueue_assets' ] );
	}

	/**
	 * Register Tools submenu page.
	 */
	public static function register_menu(): void {
		add_management_page(
			__( 'Snippet Converter', 'snippet-converter-for-perfmatters' ),
			__( 'Snippet Converter', 'snippet-converter-for-perfmatters' ),
			'manage_options',
			self::PAGE_SLUG,
			[ self::class, 'render_page' ]
		);
	}

	/**
	 * Enqueue admin assets on the converter screen.
	 *
	 * @param string $hook_suffix Current admin page hook.
	 */
	public static function enqueue_assets( string $hook_suffix ): void {
		if ( $hook_suffix !== 'tools_page_' . self::PAGE_SLUG ) {
			return;
		}

		wp_enqueue_style(
			'scfp-admin',
			SCFP_URL . 'assets/admin/converter.css',
			[],
			SCFP_VERSION
		);
	}

	/**
	 * Render the converter admin page.
	 */
	public static function render_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'snippet-converter-for-perfmatters' ) );
		}

		$result = self::get_stored_result();
		?>
		<div class="wrap scfp-wrap">
			<h1><?php esc_html_e( 'Snippet Converter for Perfmatters', 'snippet-converter-for-perfmatters' ); ?></h1>

			<?php self::render_perfmatters_notice(); ?>
			<?php self::render_admin_notices(); ?>

			<p>
				<?php
				printf(
					/* translators: %s: Perfmatters documentation URL. */
					esc_html__( 'Upload a JSON export from another code snippet plugin. This tool converts it into Perfmatters PMCS import format. After downloading, import the file in Perfmatters under Code → Settings. %s', 'snippet-converter-for-perfmatters' ),
					'<a href="' . esc_url( 'https://perfmatters.io/docs/code-snippets/#import-code-snippets' ) . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Perfmatters import docs', 'snippet-converter-for-perfmatters' ) . '</a>'
				);
				?>
			</p>

			<div class="scfp-card">
				<h2><?php esc_html_e( 'Step 1: Upload source JSON', 'snippet-converter-for-perfmatters' ); ?></h2>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data">
					<input type="hidden" name="action" value="scfp_upload" />
					<?php wp_nonce_field( self::NONCE_ACTION, 'scfp_nonce' ); ?>
					<p>
						<input type="file" name="scfp_json_file" accept=".json,application/json" required />
					</p>
					<?php submit_button( __( 'Analyze JSON', 'snippet-converter-for-perfmatters' ), 'primary', 'submit', false ); ?>
				</form>
			</div>

			<?php if ( $result instanceof DetectionResult && $result->is_success() ) : ?>
				<div class="scfp-card">
					<h2><?php esc_html_e( 'Step 2: Review conversion', 'snippet-converter-for-perfmatters' ); ?></h2>
					<p>
						<strong><?php esc_html_e( 'Detected format:', 'snippet-converter-for-perfmatters' ); ?></strong>
						<?php echo esc_html( $result->adapter_label ); ?>
						&nbsp;|&nbsp;
						<strong><?php esc_html_e( 'Snippets:', 'snippet-converter-for-perfmatters' ); ?></strong>
						<?php echo esc_html( (string) count( $result->snippets ) ); ?>
					</p>

					<table class="widefat striped scfp-preview-table">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Name', 'snippet-converter-for-perfmatters' ); ?></th>
								<th><?php esc_html_e( 'Type', 'snippet-converter-for-perfmatters' ); ?></th>
								<th><?php esc_html_e( 'Code length', 'snippet-converter-for-perfmatters' ); ?></th>
								<th><?php esc_html_e( 'Warnings', 'snippet-converter-for-perfmatters' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $result->snippets as $snippet ) : ?>
								<tr>
									<td><?php echo esc_html( $snippet->name ); ?></td>
									<td><code><?php echo esc_html( $snippet->type ); ?></code></td>
									<td><?php echo esc_html( (string) strlen( $snippet->export_code() ) ); ?></td>
									<td>
										<?php
										if ( $snippet->warnings !== [] ) {
											echo esc_html( implode( ' ', $snippet->warnings ) );
										} else {
											echo '&mdash;';
										}
										?>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>

				<div class="scfp-card">
					<h2><?php esc_html_e( 'Step 3: Download Perfmatters JSON', 'snippet-converter-for-perfmatters' ); ?></h2>
					<p><?php esc_html_e( 'Imported snippets will be inactive in Perfmatters. Review and activate them one at a time.', 'snippet-converter-for-perfmatters' ); ?></p>
					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
						<input type="hidden" name="action" value="scfp_download" />
						<?php wp_nonce_field( self::NONCE_ACTION, 'scfp_nonce' ); ?>
						<?php submit_button( __( 'Download Perfmatters JSON', 'snippet-converter-for-perfmatters' ), 'primary', 'submit', false ); ?>
					</form>
					<ol class="scfp-next-steps">
						<li><?php esc_html_e( 'Open Perfmatters → Code → Settings.', 'snippet-converter-for-perfmatters' ); ?></li>
						<li><?php esc_html_e( 'Choose the downloaded JSON file.', 'snippet-converter-for-perfmatters' ); ?></li>
						<li><?php esc_html_e( 'Click Import Code Snippets.', 'snippet-converter-for-perfmatters' ); ?></li>
					</ol>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Handle JSON upload and analysis.
	 */
	public static function handle_upload(): void {
		self::assert_permissions();

		$json = self::read_uploaded_json();
		if ( is_wp_error( $json ) ) {
			self::redirect_with_notice( $json->get_error_message(), 'error' );
		}

		$detector = new FormatDetector();
		$result   = $detector->detect_from_json( $json );

		if ( ! $result->is_success() ) {
			$message = $result->errors[0] ?? __( 'Conversion failed.', 'snippet-converter-for-perfmatters' );
			self::redirect_with_notice( $message, 'error' );
		}

		self::store_result( $result );
		self::redirect_with_notice(
			sprintf(
				/* translators: %d: number of snippets. */
				_n(
					'Detected %d snippet. Review the preview and download the Perfmatters JSON.',
					'Detected %d snippets. Review the preview and download the Perfmatters JSON.',
					count( $result->snippets ),
					'snippet-converter-for-perfmatters'
				),
				count( $result->snippets )
			),
			'success'
		);
	}

	/**
	 * Handle Perfmatters JSON download.
	 */
	public static function handle_download(): void {
		self::assert_permissions();

		$result = self::get_stored_result();
		if ( ! $result instanceof DetectionResult || ! $result->is_success() ) {
			self::redirect_with_notice( __( 'No conversion preview found. Upload a JSON file first.', 'snippet-converter-for-perfmatters' ), 'error' );
		}

		$exporter = new PerfmattersExporter();
		$payload  = $exporter->export( $result->snippets );
		$json     = wp_json_encode( $payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );

		if ( ! is_string( $json ) ) {
			self::redirect_with_notice( __( 'Could not build export file.', 'snippet-converter-for-perfmatters' ), 'error' );
		}

		$filename = 'perfmatters-snippets-converted-' . gmdate( 'Y-m-d' ) . '.json';

		nocache_headers();
		header( 'Content-Description: File Transfer' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Content-Length: ' . strlen( $json ) );
		echo $json; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON file download.
		exit;
	}

	/**
	 * Show Perfmatters availability notice.
	 */
	private static function render_perfmatters_notice(): void {
		if ( Plugin::is_perfmatters_active() ) {
			$url = admin_url( 'admin.php?page=perfmatters#code/settings' );
			echo '<div class="notice notice-info"><p>';
			printf(
				/* translators: %s: Perfmatters import settings URL. */
				esc_html__( 'Perfmatters is active. After downloading, import snippets from %s.', 'snippet-converter-for-perfmatters' ),
				'<a href="' . esc_url( $url ) . '">' . esc_html__( 'Perfmatters Code Settings', 'snippet-converter-for-perfmatters' ) . '</a>'
			);
			echo '</p></div>';
			return;
		}

		echo '<div class="notice notice-warning"><p>';
		esc_html_e( 'Perfmatters is not active on this site. You can still convert JSON here, but you will need Perfmatters installed to import the downloaded file.', 'snippet-converter-for-perfmatters' );
		echo '</p></div>';
	}

	/**
	 * Render redirect notices from query args.
	 */
	private static function render_admin_notices(): void {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Display-only notice from our redirect.
		if ( empty( $_GET['scfp_notice'] ) || ! is_string( $_GET['scfp_notice'] ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Display-only notice from our redirect.
		$message = sanitize_text_field( wp_unslash( $_GET['scfp_notice'] ) );
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Display-only notice from our redirect.
		$type = isset( $_GET['scfp_type'] ) && $_GET['scfp_type'] === 'error' ? 'error' : 'success';

		printf(
			'<div class="notice notice-%1$s is-dismissible"><p>%2$s</p></div>',
			esc_attr( $type ),
			esc_html( $message )
		);
	}

	/**
	 * Permission and nonce checks.
	 */
	private static function assert_permissions(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to perform this action.', 'snippet-converter-for-perfmatters' ) );
		}

		check_admin_referer( self::NONCE_ACTION, 'scfp_nonce' );
	}

	/**
	 * @return string|\WP_Error
	 */
	private static function read_uploaded_json() {
		if ( empty( $_FILES['scfp_json_file'] ) || ! is_array( $_FILES['scfp_json_file'] ) ) {
			return new \WP_Error( 'scfp_missing_file', __( 'No JSON file uploaded.', 'snippet-converter-for-perfmatters' ) );
		}

		$file = $_FILES['scfp_json_file'];

		if ( ! empty( $file['error'] ) ) {
			return new \WP_Error( 'scfp_upload_error', __( 'File upload failed.', 'snippet-converter-for-perfmatters' ) );
		}

		$name = isset( $file['name'] ) && is_string( $file['name'] ) ? $file['name'] : '';
		if ( $name !== '' && ! str_ends_with( strtolower( $name ), '.json' ) ) {
			return new \WP_Error( 'scfp_invalid_extension', __( 'Please upload a .json file.', 'snippet-converter-for-perfmatters' ) );
		}

		$tmp_name = isset( $file['tmp_name'] ) && is_string( $file['tmp_name'] ) ? $file['tmp_name'] : '';
		if ( $tmp_name === '' || ! is_uploaded_file( $tmp_name ) ) {
			return new \WP_Error( 'scfp_invalid_upload', __( 'Invalid uploaded file.', 'snippet-converter-for-perfmatters' ) );
		}

		$contents = file_get_contents( $tmp_name );
		if ( ! is_string( $contents ) || trim( $contents ) === '' ) {
			return new \WP_Error( 'scfp_empty_file', __( 'Uploaded JSON file is empty.', 'snippet-converter-for-perfmatters' ) );
		}

		return $contents;
	}

	/**
	 * Persist detection result for preview and download.
	 */
	private static function store_result( DetectionResult $result ): void {
		$key = self::transient_key();
		set_transient(
			$key,
			[
				'adapter_id'    => $result->adapter_id,
				'adapter_label' => $result->adapter_label,
				'snippets'      => array_map(
					static function ( NormalizedSnippet $snippet ): array {
						return [
							'name'     => $snippet->name,
							'type'     => $snippet->type,
							'code'     => $snippet->code,
							'warnings' => $snippet->warnings,
						];
					},
					$result->snippets
				),
			],
			HOUR_IN_SECONDS
		);
	}

	/**
	 * Restore the stored detection result.
	 */
	private static function get_stored_result(): ?DetectionResult {
		$stored = get_transient( self::transient_key() );
		if ( ! is_array( $stored ) || empty( $stored['snippets'] ) || ! is_array( $stored['snippets'] ) ) {
			return null;
		}

		$snippets = [];
		foreach ( $stored['snippets'] as $item ) {
			if ( ! is_array( $item ) ) {
				continue;
			}

			$name = isset( $item['name'] ) && is_string( $item['name'] ) ? $item['name'] : '';
			$type = isset( $item['type'] ) && is_string( $item['type'] ) ? $item['type'] : 'php';
			$code = isset( $item['code'] ) && is_string( $item['code'] ) ? $item['code'] : '';
			$warnings = isset( $item['warnings'] ) && is_array( $item['warnings'] ) ? array_values( array_filter( $item['warnings'], 'is_string' ) ) : [];

			if ( $name === '' || $code === '' ) {
				continue;
			}

			$snippets[] = new NormalizedSnippet( $name, $type, $code, $warnings );
		}

		if ( $snippets === [] ) {
			return null;
		}

		$adapter_id    = isset( $stored['adapter_id'] ) && is_string( $stored['adapter_id'] ) ? $stored['adapter_id'] : '';
		$adapter_label = isset( $stored['adapter_label'] ) && is_string( $stored['adapter_label'] ) ? $stored['adapter_label'] : '';

		return new DetectionResult( $adapter_id, $adapter_label, $snippets );
	}

	private static function transient_key(): string {
		return self::TRANSIENT_PREFIX . (string) get_current_user_id();
	}

	/**
	 * Redirect back to the converter page with a notice.
	 */
	private static function redirect_with_notice( string $message, string $type ): void {
		$url = add_query_arg(
			[
				'page'        => self::PAGE_SLUG,
				'scfp_notice' => $message,
				'scfp_type'   => $type,
			],
			admin_url( 'tools.php' )
		);

		wp_safe_redirect( $url );
		exit;
	}
}
