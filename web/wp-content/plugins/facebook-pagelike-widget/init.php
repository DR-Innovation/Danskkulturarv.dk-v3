<?php
/*
 * SOFTWARE LICENSE INFORMATION
 *
 * Copyright (c) 2017 Buttonizer, all rights reserved.
 *
 * This file is part of Buttonizer
 *
 * For detailed information regarding to the licensing of
 * this software, please review the license.txt or visit:
 * https://buttonizer.io/license/
 */

use BZSocialFeeds\Core\InitBootstrap;

// Boot shared hooks (admin, redirect, page data, embed script, shortcode, admin bar, REST API)
InitBootstrap::boot(
    \BZSocialFeeds\Admin\Admin::class,
    \BZSocialFeeds\Api\Api::class
);
