<?php

declare(strict_types=1);

/*
 * This file is part of the WooCommerce Product Upload with Uppy WordPress plugin.
 *
 * (ɔ) Frugan <dev@frugan.it>
 *
 * This source file is subject to the GNU GPLv3 or later license that is bundled
 * with this source code in the file LICENSE.
 */

namespace WpSpaghetti\WCPUWU;

/*
 * Plugin Name: WooCommerce Product Upload with Uppy
 * Plugin URI: https://github.com/wp-spaghetti/wc-product-upload-with-uppy
 * Description: Async file uploads with Uppy for WooCommerce downloadable products
 * Version: 0.1.0
 * Text Domain: wc-product-upload-with-uppy
 * Requires Plugins: woocommerce
 * Author: Frugan
 * Author URI: https://github.com/wp-spaghetti
 * Requires at least: 6.0
 * Tested up to: 6.8
 * Requires PHP: 8.1
 * License: GPL-3.0-or-later
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * Donate link: https://buymeacoff.ee/frugan.
 */

if (!\defined('ABSPATH')) {
    exit;
}

// Plugin constants
\define('WPSPAGHETTI_WCPUWU_VERSION', '0.1.0');
\define('WPSPAGHETTI_WCPUWU_FILE', __FILE__);
\define('WPSPAGHETTI_WCPUWU_PATH', plugin_dir_path(__FILE__));
\define('WPSPAGHETTI_WCPUWU_URL', plugin_dir_url(__FILE__));

// Autoload
require_once __DIR__.'/vendor/autoload.php';

// Initialize plugin
add_action('plugins_loaded', static function (): void {
    // Check if WooCommerce is active
    if (!class_exists('WooCommerce')) {
        add_action('admin_notices', static function (): void {
            echo '<div class="error"><p>'
                 .esc_html__('WooCommerce Product Upload with Uppy requires WooCommerce to be installed and active.', 'wc-product-upload-with-uppy')
                 .'</p></div>';
        });

        return;
    }

    // Initialize components
    new Assets();
    new TusServer();
    new Admin();
});
