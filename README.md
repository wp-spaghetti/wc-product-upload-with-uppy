![](.wordpress-org/banner-1544x500.jpg)

![GitHub Downloads (all assets, all releases)](https://img.shields.io/github/downloads/wp-spaghetti/wc-product-upload-with-uppy/total)
![GitHub Actions Workflow Status](https://github.com/wp-spaghetti/wc-product-upload-with-uppy/actions/workflows/main.yml/badge.svg)
<!--
![Coverage Status](https://img.shields.io/codecov/c/github/wp-spaghetti/wc-product-upload-with-uppy)
![Quality Gate Status](https://sonarcloud.io/api/project_badges/measure?project=wp-spaghetti_wc-product-upload-with-uppy&metric=alert_status)
![Security Rating](https://sonarcloud.io/api/project_badges/measure?project=wp-spaghetti_wc-product-upload-with-uppy&metric=security_rating)
![Known Vulnerabilities](https://snyk.io/test/github/wp-spaghetti/wc-product-upload-with-uppy/badge.svg)
-->
![GitHub Issues](https://img.shields.io/github/issues/wp-spaghetti/wc-product-upload-with-uppy)
![GitHub Release](https://img.shields.io/github/v/release/wp-spaghetti/wc-product-upload-with-uppy)
![License](https://img.shields.io/github/license/wp-spaghetti/wc-product-upload-with-uppy)
<!--
![PHP Version](https://img.shields.io/badge/php->=8.0-blue)
![Code Climate](https://img.shields.io/codeclimate/maintainability/wp-spaghetti/wc-product-upload-with-uppy)
-->

# WooCommerce Product Upload with Uppy (WordPress Plugin)

Async file uploads with Uppy for WooCommerce downloadable products.

The plugin automatically replaces the "Choose file" button in WooCommerce downloadable products with Uppy's upload interface, enabling chunked uploads for large files.

## Requirements

- PHP ^8.1
- WordPress ^6.0
- [WooCommerce](https://wordpress.org/plugins/woocommerce/) ^10.0

## Installation

You can install the plugin in three ways: manually, via Composer from [WPackagist](https://wpackagist.org), or via Composer from [GitHub Releases](../../releases).

<details>
<summary>Manual Installation</summary>

1. Go to the [Releases](../../releases) section of this repository.
2. Download the latest release zip file.
3. Log in to your WordPress admin dashboard.
4. Navigate to `Plugins` > `Add New`.
5. Click `Upload Plugin`.
6. Choose the downloaded zip file and click `Install Now`.

</details>

<details>
<summary>Installation via Composer from WPackagist</summary>

If you use Composer to manage WordPress plugins, you can install it from [WordPress Packagist](https://wpackagist.org):

1. Open your terminal.
2. Navigate to the root directory of your WordPress installation.
3. Ensure your `composer.json` file has the following configuration: *

```json
{
    "require": {
        "composer/installers": "^1.0 || ^2.0",
        "wpackagist-plugin/wc-product-upload-with-uppy": "^0.1"
    },
    "extra": {
        "installer-paths": {
            "wp-content/plugins/{$name}/": [
               "type:wordpress-plugin"
            ]
        }
    }
}
```
4. Run the following command:

```sh
composer update
```

<sub><i>
_Note:_  
_* `composer/installers` might already be required by another dependency._
</i></sub>
</details>

<details>
<summary>Installation via Composer from GitHub Releases</summary>

If you use Composer to manage WordPress plugins, you can install it from this repository directly.

**Standard Version** (uses WordPress update system):

1. Open your terminal.
2. Navigate to the root directory of your WordPress installation.
3. Ensure your `composer.json` file has the following configuration: *

```json
{
    "require": {
        "composer/installers": "^1.0 || ^2.0",
        "wp-spaghetti/wc-product-upload-with-uppy": "^0.1"
    },
    "repositories": [
        {
            "type": "package",
            "package": {
                "name": "wp-spaghetti/wc-product-upload-with-uppy",
                "version": "0.1.0",
                "type": "wordpress-plugin",
                "dist": {
                    "url": "https://github.com/wp-spaghetti/wc-product-upload-with-uppy/releases/download/v0.1.0/wc-product-upload-with-uppy.zip",
                    "type": "zip"
                }
            }
        }
    ],
    "extra": {
        "installer-paths": {
            "wp-content/plugins/{$name}/": [
               "type:wordpress-plugin"
            ]
        }
    }
}
```

**Version with Git Updater** (uses Git Updater Lite for updates):

For installations that need updates managed via Git instead of WordPress.org, use the `--with-git-updater` version:

```json
{
    "require": {
        "composer/installers": "^1.0 || ^2.0",
        "wp-spaghetti/wc-product-upload-with-uppy": "^0.1"
    },
    "repositories": [
        {
            "type": "package",
            "package": {
                "name": "wp-spaghetti/wc-product-upload-with-uppy",
                "version": "0.1.0",
                "type": "wordpress-plugin",
                "dist": {
                    "url": "https://github.com/wp-spaghetti/wc-product-upload-with-uppy/releases/download/v0.1.0/wc-product-upload-with-uppy--with-git-updater.zip",
                    "type": "zip"
                }
            }
        }
    ],
    "extra": {
        "installer-paths": {
            "wp-content/plugins/{$name}/": [
               "type:wordpress-plugin"
            ]
        }
    }
}
```

4. Run the following command:

```sh
composer update
```

<sub><i>
_Note:_  
_* `composer/installers` might already be required by another dependency._  
_* The `--with-git-updater` version includes [Git Updater Lite](https://github.com/afragen/git-updater-lite) for automatic updates detection, while the standard version relies on WordPress.org update system._
</i></sub>
</details>

## Configuration

Add these constants to your `wp-config.php` file to customize the plugin behavior:

```php
// Chunk size for TUS uploads (in bytes)
// Default: 2097152 (2MB)
define('WPSPAGHETTI_WCPUWU_CHUNK_SIZE', 2 * 1024 * 1024);

// Maximum file size allowed for upload (in bytes)
// Default: null (no limit, respects PHP upload_max_filesize and post_max_size)
// Example: 100MB limit
define('WPSPAGHETTI_WCPUWU_MAX_FILE_SIZE', 100 * 1024 * 1024);

// Allowed file types (array of MIME types or file extensions)
// Default: [] (all types allowed)
// Example: ['application/pdf', '.pdf'] (Only PDF files)
define('WPSPAGHETTI_WCPUWU_ALLOWED_FILE_TYPES', ['application/pdf', '.pdf']);

// Example: Multiple document types
// define('WPSPAGHETTI_WCPUWU_ALLOWED_FILE_TYPES', [
//     'application/pdf',
//     'application/zip',
//     'application/msword',
//     'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
//     '.pdf',
//     '.zip',
//     '.doc',
//     '.docx'
// ]);

// Enable cache busting for assets (useful during development)
// Default: false
define('WPSPAGHETTI_WCPUWU_CACHE_BUSTING', true);
```

## More info

See [LINKS](docs/LINKS.md) file.

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for a detailed list of changes for each release.

We follow [Semantic Versioning](https://semver.org/) and use [Conventional Commits](https://www.conventionalcommits.org/) to automatically generate our changelog.

### Release Process

- **Major versions** (1.0.0 → 2.0.0): Breaking changes
- **Minor versions** (1.0.0 → 1.1.0): New features, backward compatible
- **Patch versions** (1.0.0 → 1.0.1): Bug fixes, backward compatible

All releases are automatically created when changes are pushed to the `main` branch, based on commit message conventions.

## Contributing

For your contributions please use:

- [Conventional Commits](https://www.conventionalcommits.org)
- [Pull request workflow](https://docs.github.com/en/get-started/exploring-projects-on-github/contributing-to-a-project)

See [CONTRIBUTING](.github/CONTRIBUTING.md) for detailed guidelines.

## Sponsor

[<img src="https://cdn.buymeacoffee.com/buttons/v2/default-yellow.png" width="200" alt="Buy Me A Coffee">](https://buymeacoff.ee/frugan)

## License

(ɔ) Copyleft 2025 [Frugan](https://frugan.it).  
[GNU GPLv3](https://choosealicense.com/licenses/gpl-3.0/), see [LICENSE](LICENSE) file.
