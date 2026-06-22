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

namespace BZSocialFeeds\Api;

use BZSocialFeeds\Core\Api\BaseApi;

class Api extends BaseApi
{
    /**
     * Register API endpoints.
     *
     * All routes are shared (Connect, Disconnect, Sync, Settings, Editor, Analytics)
     * and registered by BaseApi. No plugin-specific routes needed.
     */
    public function __construct()
    {
        parent::__construct();
    }
}
