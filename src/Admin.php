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

class Admin
{
    public function __construct()
    {
        add_action('admin_footer', [$this, 'addUppyModal']);
    }

    public function addUppyModal(): void
    {
        $screen = get_current_screen();
        if (!$screen || 'product' !== $screen->post_type) {
            return;
        }
        ?>
        <div id="wpspaghetti-wcpuwu-uppy-dashboard"></div>
        <?php
    }
}
