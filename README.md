# Snippet Converter for Perfmatters

Convert third-party code snippet JSON exports into [Perfmatters](https://perfmatters.io/) PMCS import format.

## Requirements

- WordPress 5.8+
- PHP 8.1+
- Perfmatters (required only for the final import step, not for conversion)

## Usage

1. Install and activate the plugin.
2. Go to **Tools → Snippet Converter**.
3. Upload a `.json` export from another snippet plugin.
4. Review the preview and download the converted file.
5. Import in **Perfmatters → Code → Settings → Import Code Snippets**.

## Development

```bash
composer install
composer lint
composer test
composer run dist-zip
```

## License

GPL-2.0-or-later
