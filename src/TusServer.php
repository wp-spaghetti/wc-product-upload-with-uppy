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

use TusPhp\Exception\TusException;
use TusPhp\Tus\Server;
use TusPhp\Tus\Server as TusPhpServer;

class TusServer
{
    private ?TusPhpServer $tusPhpServer = null;

    private string $tusDir;

    public function __construct()
    {
        add_action('init', [$this, 'setupDirectories']);
        add_action('parse_request', [$this, 'handleTusRequest'], 1);
        add_action('rest_api_init', [$this, 'registerFileUrlRoute']);
    }

    public function setupDirectories(): void
    {
        // TUS directory for temporary chunked uploads
        $this->tusDir = sys_get_temp_dir().'/wc-product-upload-with-uppy';

        // Create TUS upload directory if it doesn't exist
        if (!file_exists($this->tusDir)) {
            wp_mkdir_p($this->tusDir);
        }
    }

    public function handleTusRequest(\WP $wp): void
    {
        // Check if this is a TUS request
        $requestUri = $_SERVER['REQUEST_URI'] ?? '';
        if (!str_contains($requestUri, '/wp-json/wc-product-upload-with-uppy/v1/upload')) {
            return;
        }

        // Check permissions
        if (!$this->checkPermissions()) {
            status_header(403);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Forbidden']);

            exit;
        }

        try {
            $server = $this->getTusServer();
            $response = $server->serve();

            // Send response using tus-php built-in method
            $response->send();

            exit;
        } catch (TusException $tusException) {
            status_header(500);
            header('Content-Type: application/json');
            echo json_encode(['error' => $tusException->getMessage()]);

            exit;
        }
    }

    public function registerFileUrlRoute(): void
    {
        // Register REST endpoint for file URL retrieval
        register_rest_route('wc-product-upload-with-uppy/v1', '/file-url/(?P<key>[^/]+)', [
            'methods' => 'GET',
            'callback' => [$this, 'getFileUrl'],
            'permission_callback' => [$this, 'checkPermissions'],
        ]);
    }

    public function checkPermissions(): bool
    {
        return current_user_can('edit_products');
    }

    public function getFileUrl(\WP_REST_Request $wprestRequest): \WP_Error|\WP_REST_Response
    {
        $key = $wprestRequest->get_param('key');
        $fileUrl = get_transient('wc_product_upload_with_uppy_'.$key);

        if (false === $fileUrl) {
            return new \WP_Error('file_not_found', 'File URL not found', ['status' => 404]);
        }

        // Delete transient after retrieval
        delete_transient('wc_product_upload_with_uppy_'.$key);

        return new \WP_REST_Response(['url' => $fileUrl], 200);
    }

    private function getTusServer(): TusPhpServer
    {
        if (!$this->tusPhpServer instanceof Server) {
            $this->tusPhpServer = new TusPhpServer('file');
            $this->tusPhpServer->setApiPath('/wp-json/wc-product-upload-with-uppy/v1/upload');
            $this->tusPhpServer->setUploadDir($this->tusDir);

            // Set max upload size to match PHP settings
            $maxUploadSize = \defined('WPSPAGHETTI_WCPUWU_MAX_FILE_SIZE')
                ? (int) WPSPAGHETTI_WCPUWU_MAX_FILE_SIZE
                : min(
                    wp_max_upload_size(),
                    $this->parseSize(\ini_get('upload_max_filesize')),
                    $this->parseSize(\ini_get('post_max_size'))
                );
            $this->tusPhpServer->setMaxUploadSize($maxUploadSize);

            // Event handler when upload is complete
            $this->tusPhpServer->event()->addListener('tus-server.upload.complete', function ($event): void {
                $uploadDir = wp_upload_dir();
                $filePath = $event->getFile()->getFilePath();

                // Get original filename from metadata
                $metadata = $event->getFile()->details();
                $originalName = $metadata['name'] ?? basename($filePath);

                // Get post_id from request headers
                $post_id = isset($_SERVER['HTTP_X_POST_ID']) ? (int) $_SERVER['HTTP_X_POST_ID'] : 0;

                // Sanitize and ensure unique filename
                $fileName = wp_unique_filename($uploadDir['path'], $originalName);

                // Check if woocommerce_uploads directory exists, otherwise use standard uploads
                $wcDir = $uploadDir['basedir'].'/woocommerce_uploads';
                if (!file_exists($wcDir)) {
                    wp_mkdir_p($wcDir);
                }

                // Create year/month subdirectories like WooCommerce does
                if ($post_id > 0) {
                    $post = get_post($post_id);
                    $post_date = $post && isset($post->post_date) ? strtotime($post->post_date) : time();
                } else {
                    $post_date = time();
                }

                $subdir = '/'.date('Y', $post_date).'/'.date('m', $post_date);
                $finalDir = $wcDir.$subdir;

                if (!file_exists($finalDir)) {
                    wp_mkdir_p($finalDir);
                }

                $targetPath = $finalDir.'/'.$fileName;

                // Move file to final destination (handle cross-filesystem moves)
                if (@rename($filePath, $targetPath) || (@copy($filePath, $targetPath) && @unlink($filePath))) {
                    // Clean up TUS metadata
                    @unlink($filePath.'.json');

                    // Check if attachment with same file already exists
                    global $wpdb;
                    $existing_attachment = $wpdb->get_var($wpdb->prepare(
                        "SELECT post_id FROM {$wpdb->postmeta}
                        WHERE meta_key = '_wp_attached_file'
                        AND meta_value = %s
                        LIMIT 1",
                        'woocommerce_uploads'.$subdir.'/'.$fileName
                    ));

                    if ($existing_attachment) {
                        // Use existing attachment
                        $fileUrl = wp_get_attachment_url($existing_attachment);
                        $fileKey = $event->getFile()->getKey();
                        set_transient('wc_product_upload_with_uppy_'.$fileKey, $fileUrl, 3600);

                        // Clean up uploaded file since we're using existing one
                        @unlink($targetPath);

                        return;
                    }

                    // Insert into media library
                    $fileType = wp_check_filetype($fileName);
                    $attachment = [
                        'guid' => $uploadDir['baseurl'].'/woocommerce_uploads'.$subdir.'/'.$fileName,
                        'post_mime_type' => $fileType['type'],
                        'post_title' => preg_replace('/\.[^.]+$/', '', $fileName),
                        'post_content' => '',
                        'post_status' => 'inherit',
                    ];

                    $attachmentId = wp_insert_attachment($attachment, $targetPath);

                    if (!is_wp_error($attachmentId)) {
                        require_once ABSPATH.'wp-admin/includes/image.php';
                        $attachmentData = wp_generate_attachment_metadata($attachmentId, $targetPath);
                        wp_update_attachment_metadata($attachmentId, $attachmentData);

                        // Store URL using the file key directly (no hash)
                        $fileUrl = wp_get_attachment_url($attachmentId);
                        $fileKey = $event->getFile()->getKey();
                        set_transient('wc_product_upload_with_uppy_'.$fileKey, $fileUrl, 3600);
                    }
                }
            });
        }

        return $this->tusPhpServer;
    }

    private function parseSize(string $size): int
    {
        $unit = strtolower(substr($size, -1));
        $value = (int) $size;

        return match ($unit) {
            'g' => $value * 1024 * 1024 * 1024,
            'm' => $value * 1024 * 1024,
            'k' => $value * 1024,
            default => $value,
        };
    }
}
