# Snippet Converter for Perfmatters

Convert third-party code snippet JSON exports into [Perfmatters](https://perfmatters.io/) PMCS import format.

I coded this for my sites and wanted to share it. It has saved me a TON of time. 

Currently, Perfmatters only allows you to import snippets that were created within Perfmatters. This plugin allows you to get around that by converting third-party code snippet JSON exports into the Perfmatters PMCS import format.

In other words, if you are currently using a code snippets plugin that allows you to export snippets to a JSON file, you can easily import those snippets into Perfmatters.

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

## Software Use Disclaimer

- *Use at Your Own Risk:* All software, plugins, themes, code snippets, and tools provided or recommended are offered "as is" without any warranties, express or implied. You assume full responsibility for any risks associated with downloading, installing, configuring, or using the software.
- *Limitation of Liability:* In no event shall we (or any contributors, affiliates, or licensors) be liable for any direct, indirect, incidental, special, consequential, or exemplary damages, including but not limited to loss of data, business interruption, or any other losses arising from your use (or inability to use) the software, even if advised of the possibility of such damages.

## AI Disclaimer
- This plugin is 100% vibe-coded, and the code has not been reviewed.
- WordPress coding standards/rules have been followed with effort and to the best of my ability.

## Affiliate Disclaimer
- This plugin is not affiliated with, endorsed by, or officially connected to the Perfmatters team in any way. Perfmatters is a trademark of its respective owner.
