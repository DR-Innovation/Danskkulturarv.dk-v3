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

class Api
{
    /**
     * Register API endpoints
     */
    public function __construct()
    {
        // Site synchronization
        (new Connection\Sync())->registerRoute();
        (new Connection\Disconnect())->registerRoute();
        (new Connection\Connect())->registerRoute();

        // Plugin settings
        (new Settings\UpdateSettings())->registerRoute();

        // Editor
        (new Connection\StartEditorSession())->registerRoute();

        // Analytics
        (new Analytics\Overview())->registerRoute();
    }
}

