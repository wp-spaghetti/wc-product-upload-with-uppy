import Uppy from '@uppy/core';
import Dashboard from '@uppy/dashboard';
import Tus from '@uppy/tus';

jQuery(function($) {
    // Check if configuration is available
    if (typeof WPSPAGHETTI_WCPUWU === 'undefined') {
        console.error('WPSPAGHETTI_WCPUWU configuration not found');
        return;
    }

    let currentFilePathField;
    let uppyInstance;

    // Initialize Uppy
    function initUppy() {
        if (uppyInstance) {
            return uppyInstance;
        }

        // Get post ID from URL or hidden field
        const urlParameters = new URLSearchParams(globalThis.location.search);
        const postId = urlParameters.get('post') || jQuery('#post_ID').val();

        const restrictions = {
            maxNumberOfFiles: 1
        };

        // Add max file size restriction if configured
        if (WPSPAGHETTI_WCPUWU.maxFileSize) {
            restrictions.maxFileSize = Number.parseInt(WPSPAGHETTI_WCPUWU.maxFileSize);
        }

        // Add allowed file types restriction if configured
        if (WPSPAGHETTI_WCPUWU.allowedFileTypes && WPSPAGHETTI_WCPUWU.allowedFileTypes.length > 0) {
            restrictions.allowedFileTypes = WPSPAGHETTI_WCPUWU.allowedFileTypes;
        }

        uppyInstance = new Uppy({
            restrictions: restrictions,
            autoProceed: false
        })
        .use(Dashboard, {
            inline: false,
            target: '#wpspaghetti-wcpuwu-uppy-dashboard',
            trigger: undefined,
            showProgressDetails: true,
            proudlyDisplayPoweredByUppy: false,
            height: 450
        })
        .use(Tus, {
            endpoint: WPSPAGHETTI_WCPUWU.tusEndpoint,
            headers: {
                'X-WP-Nonce': WPSPAGHETTI_WCPUWU.nonce
            },
            removeFingerprintOnSuccess: true,
            retryDelays: [0, 1000, 3000, 5000],
            onBeforeRequest: function(request) {
                request.setHeader('X-Post-ID', postId);
            }
        });

        // Handle successful upload
        uppyInstance.on('upload-success', async (file, response) => {
            if (currentFilePathField) {
                // Get file URL from server
                const uploadLocation = response.uploadURL;
                const fileKey = uploadLocation.split('/').pop();
                
                try {
                    const urlResponse = await fetch(
                        WPSPAGHETTI_WCPUWU.tusEndpoint.replace('/upload', '/file-url/' + fileKey),
                        {
                            headers: {
                                'X-WP-Nonce': WPSPAGHETTI_WCPUWU.nonce
                            }
                        }
                    );
                    
                    if (urlResponse.ok) {
                        const data = await urlResponse.json();
                        currentFilePathField.val(data.url).trigger('change');
                    } else {
                        console.error('Error fetching file URL:', urlResponse.status);
                        alert('Error retrieving file URL');
                    }
                } catch (error) {
                    console.error('Error fetching file URL:', error);
                    alert('Error retrieving file URL');
                }
            }
            uppyInstance.getPlugin('Dashboard').closeModal();
        });

        // Handle upload errors
        uppyInstance.on('upload-error', (file, error) => {
            console.error('Upload error:', error);
            alert('Upload failed: ' + (error.message || 'Unknown error'));
        });

        return uppyInstance;
    }

    // Wait for WooCommerce to attach its handlers, then override
    function attachUppyHandler() {
        // Remove WooCommerce default handler for upload button
        $(document.body).off('click', '.upload_file_button');

        // Add Uppy handler for upload button with higher priority
        $(document.body).on('click', '.upload_file_button', function(event) {
            event.preventDefault();
            event.stopImmediatePropagation();

            const $button = $(this);
            currentFilePathField = $button.closest('tr').find('td.file_url input');

            if (!uppyInstance) {
                uppyInstance = initUppy();
            }

            // Reset Uppy before opening to avoid flickering
            uppyInstance.clear();
            uppyInstance.getPlugin('Dashboard').openModal();

            return false;
        });
    }

    // Attach handler after DOM is ready and WooCommerce scripts are loaded
    attachUppyHandler();
});
