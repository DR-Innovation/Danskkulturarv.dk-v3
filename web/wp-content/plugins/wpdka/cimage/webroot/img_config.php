<?php
/**
 * Configuration for img.php, name the config file the same as your img.php and
 * append _config. If you are testing out some in imgtest.php then label that
 * config-file imgtest_config.php.
 *
 */
return array(

    /**
     * Set mode as 'strict', 'production' or 'development'.
     *
     * Default values:
     *  mode: 'production'
     */
    'mode' => 'development',
    //'mode' => 'production', // 'development', 'strict'



    /**
     * Where are the sources for the classfiles.
     *
     * Default values:
     *  autoloader:  null     // used from v0.6.2
     *  cimage_class: null    // used until v0.6.1
     */
    'autoloader'   =>  __DIR__ . '/../autoload.php',
    //'cimage_class' =>  __DIR__ . '/../CImage.php',



    /**
     * Paths, where are the images stored and where is the cache.
     * End all paths with a slash.
     *
     * Default values:
     *  image_path: __DIR__ . '/img/'
     *  cache_path: __DIR__ . '/../cache/'
     *  alias_path: null
     */
    'image_path'   =>  __DIR__ . '/',
    'cache_path'   =>  __DIR__ . '/../cache/',
    //'alias_path'   =>  __DIR__ . '/img/alias/',



    /**
    * Use password to protect from missusage, send &pwd=... or &password=..
    * with the request to match the password or set to false to disable.
    * Passwords are only used together with the options for remote download
    * and aliasing.
    *
    * Default values.
    *  password: false // as in do not use password
    */
    //'password' => false, // "secret-password",



    /**
     * Allow or disallow downloading of remote files, images available on
     * some remote server. Default is to disallow.
     *
     * Default values.
     *  remote_allow:    false
     *  remote_pattern:  null  // use default values from CImage
     */
    'remote_allow'    => true,
    'remote_pattern'  => '#^https?://#',



    /**
     * A regexp for validating characters in the image or alias filename.
     *
     * Default value:
     *  valid_filename:  '#^[a-z0-9A-Z-/_\.:]+$#'
     *  valid_aliasname: '#^[a-z0-9A-Z-_]+$#'
     */
     //'valid_filename'  => '#^[a-z0-9A-Z-/_\.:]+$#',
     //'valid_aliasname' => '#^[a-z0-9A-Z-_]+$#',



     /**
     * Check that the imagefile is a file below 'image_path' using realpath().
     * Security constraint to avoid reaching images outside image_path.
     * This means that symbolic links to images outside the image_path will fail.
     *
     * Default value:
     *  image_path_constraint: true
     */
     //'image_path_constraint' => false,



     /**
     * Set default timezone.
     *
     * Default values.
     *  default_timezone: ini_get('default_timezone') or 'UTC'
     */
    //'default_timezone' => 'UTC',



    /**
     * Max image dimensions, larger dimensions results in 404.
     * This is basically a security constraint to avoid using resources on creating
     * large (unwanted) images.
     *
     * Default values.
     *  max_width:  2000
     *  max_height: 2000
     */
    //'max_width'     => 2000,
    //'max_height'    => 2000,



    /**
     * Set default background color for all images. Override it using
     * option bgColor.
     * Colorvalue is 6 digit hex string between 000000-FFFFFF
     * or 8 digit hex string if using the alpha channel where
     * the alpha value is between 00 (opaqe) and 7F (transparent),
     * that is between 00000000-FFFFFF7F.
     *
     * Default values.
     *  background_color: As specified by CImage
     */
    //'background_color' => "FFFFFF",
    //'background_color' => "FFFFFF7F",



    /**
     * Post processing of images using external tools, set to true or false
     * and set command to be executed.
     *
     * Default values.
     *
     *  png_filter:        false
     *  png_filter_cmd:    '/usr/local/bin/optipng -q'
     *
     *  png_deflate:       false
     *  png_deflate_cmd:   '/usr/local/bin/pngout -q'
     *
     *  jpeg_optimize:     false
     *  jpeg_optimize_cmd: '/usr/local/bin/jpegtran -copy none -optimize'
     */
    /*
    'postprocessing' => array(
        'png_filter'        => false,
        'png_filter_cmd'    => '/usr/local/bin/optipng -q',

        'png_deflate'       => false,
        'png_deflate_cmd'   => '/usr/local/bin/pngout -q',

        'jpeg_optimize'     => false,
        'jpeg_optimize_cmd' => '/usr/local/bin/jpegtran -copy none -optimize',
    ),
    */



    /**
     * Create custom convolution expressions, matrix 3x3, divisor and
     * offset.
     *
     * Default values.
     *  convolution_constant: array()
     */
    /*
    'convolution_constant' => array(
        //'sharpen'       => '-1,-1,-1, -1,16,-1, -1,-1,-1, 8, 0',
        //'sharpen-alt'   => '0,-1,0, -1,5,-1, 0,-1,0, 1, 0',
    ),
    */



    /**
     * Prevent leeching of images by controlling who can access them from where.
     * Default it to allow hotlinking.
     * Password apply when hotlinking is disallowed, use password to allow.
     * The whitelist is an array of regexpes for allowed hostnames that can
     * hotlink images.
     *
     * Default values.
     *  allow_hotlinking:     true
     *  hotlinking_whitelist: array()
     */
     /*
    'allow_hotlinking' => false,
    'hotlinking_whitelist' => array(
        '#^localhost$#',
        '#^dbwebb\.se$#',
    ),
    */



    /**
     * Create custom shortcuts for more advanced expressions.
     *
     * Default values.
     *  shortcut: array(
     *      'sepia' => "&f=grayscale&f0=brightness,-10&f1=contrast,-20&f2=colorize,120,60,0,0&sharpen",
     *  )
     */
     /*
    'shortcut' => array(
        'sepia' => "&f=grayscale&f0=brightness,-10&f1=contrast,-20&f2=colorize,120,60,0,0&sharpen",
    ),*/



    /**
     * Predefined size constants.
     *
     * These can be used together with &width or &height to create a constant value
     * for a width or height where can be changed in one place.
     * Useful when your site changes its layout or if you have a grid to fit images into.
     *
     * Example:
     *  &width=w1  // results in width=613
     *  &width=c2  // results in spanning two columns with a gutter, 30*2+10=70
     *  &width=c24 // results in spanning whole grid 24*30+((24-1)*10)=950
     *
     * Default values.
     *  size_constant: As specified by the function below.
     */
    /*
    'size_constant' => function () {

        // Set sizes to map constant to value, easier to use with width or height
        $sizes = array(
          'w1' => 613,
          'w2' => 630,
        );

        // Add grid column width, useful for use as predefined size for width (or height).
        $gridColumnWidth = 30;
        $gridGutterWidth = 10;
        $gridColumns     = 24;

        for ($i = 1; $i <= $gridColumns; $i++) {
            $sizes['c' . $i] = ($gridColumnWidth + $gridGutterWidth) * $i - $gridGutterWidth;
        }

        return $sizes;
    },*/



    /**
     * Predefined aspect ratios.
     *
     * Default values.
     *  aspect_ratio_constant: As the function below.
     */
    /*'aspect_ratio_constant' => function () {
        return array(
            '3:1'   => 3/1,
            '3:2'   => 3/2,
            '4:3'   => 4/3,
            '8:5'   => 8/5,
            '16:10' => 16/10,
            '16:9'  => 16/9,
            'golden' => 1.618,
        );
    },*/
);
