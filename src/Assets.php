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

class Assets
{
    public function __construct()
    {
        add_action('admin_enqueue_scripts', [$this, 'enqueueAssets']);
    }

    public function enqueueAssets($hook): void
    {
        // Only load on product edit pages
        if (!\in_array($hook, ['post.php', 'post-new.php'], true)) {
            return;
        }

        global $post;
        if (!$post || 'product' !== $post->post_type) {
            return;
        }

        $cache_busting = \defined('WPSPAGHETTI_WCPUWU_CACHE_BUSTING') && (bool) WPSPAGHETTI_WCPUWU_CACHE_BUSTING;

        // Enqueue Uppy core CSS
        wp_enqueue_style(
            'uppy-core',
            WPSPAGHETTI_WCPUWU_URL.'assets/css/uppy-core.min'.($cache_busting ? '.'.filemtime(\dirname(__DIR__).'/assets/css/uppy-core.min.css') : '').'.css',
            [],
            WPSPAGHETTI_WCPUWU_VERSION
        );

        // Enqueue Uppy dashboard CSS
        wp_enqueue_style(
            'uppy-dashboard',
            WPSPAGHETTI_WCPUWU_URL.'assets/css/uppy-dashboard.min'.($cache_busting ? '.'.filemtime(\dirname(__DIR__).'/assets/css/uppy-dashboard.min.css') : '').'.css',
            ['uppy-core'],
            WPSPAGHETTI_WCPUWU_VERSION
        );

        // Enqueue custom CSS
        wp_enqueue_style(
            'wc-product-upload-with-uppy',
            WPSPAGHETTI_WCPUWU_URL.'assets/css/admin.min'.($cache_busting ? '.'.filemtime(\dirname(__DIR__).'/assets/css/admin.min.css') : '').'.css',
            ['uppy-dashboard'],
            WPSPAGHETTI_WCPUWU_VERSION
        );

        // Enqueue admin script (must load after WooCommerce meta-boxes script)
        wp_enqueue_script(
            'wc-product-upload-with-uppy',
            WPSPAGHETTI_WCPUWU_URL.'assets/js/admin.min'.($cache_busting ? '.'.filemtime(\dirname(__DIR__).'/assets/js/admin.min.js') : '').'.js',
            ['jquery', 'wc-admin-meta-boxes'],
            WPSPAGHETTI_WCPUWU_VERSION,
            true
        );

        // Pass configuration to JavaScript
        wp_localize_script('wc-product-upload-with-uppy', 'WPSPAGHETTI_WCPUWU', [
            'tusEndpoint' => rest_url('wc-product-upload-with-uppy/v1/upload'),
            'nonce' => wp_create_nonce('wp_rest'),
            'maxFileSize' => \defined('WPSPAGHETTI_WCPUWU_MAX_FILE_SIZE') ? (int) WPSPAGHETTI_WCPUWU_MAX_FILE_SIZE : null, // null = no limit
            'allowedFileTypes' => \defined('WPSPAGHETTI_WCPUWU_ALLOWED_FILE_TYPES') ? json_decode((string) WPSPAGHETTI_WCPUWU_ALLOWED_FILE_TYPES, true) : [], // [] = all types
        ]);
    }
}
