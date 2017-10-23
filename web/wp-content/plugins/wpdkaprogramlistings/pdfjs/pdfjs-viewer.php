<?php

//tell wordpress to register the pdfjs-viewer shortcode
add_shortcode('pdfjs-viewer', 'pdfjs_handler');
add_shortcode('pdfbuttons-viewer', 'pdfbuttons_handler');
add_shortcode('pdfembed-viewer', 'pdfembed_handler');

function pdfjs_handler($args)
{
    //set defaults
  $args = shortcode_atts(array(
    'url' => 'bad-url.pdf',
    'fullscreen' => 'false',
    'download' => 'false',
    'print' => 'false',
    'openfile' => 'false',
    'iframe' => 'true',
    'embed' => 'false',
    'share' => 'false'
  ), $args);
  //run function that actually does the work of the plugin
  $pdfjs_output = pdfjs_function($args);
  //send back text to replace shortcode in post
  return $pdfjs_output;
}

function pdfbuttons_handler($args)
{
    //set defaults
  $args = shortcode_atts(array(
    'url' => 'bad-url.pdf',
    'fullscreen' => 'false',
    'download' => 'true',
    'print' => 'true',
    'openfile' => 'false',
    'iframe' => 'false',
    'embed' => 'false',
    'share' => 'true'
  ), $args);
  //run function that actually does the work of the plugin
  $pdfjs_output = pdfjs_function($args);
  //send back text to replace shortcode in post
  return $pdfjs_output;
}

function pdfembed_handler($args)
{
    //set defaults
  $args = shortcode_atts(array(
    'url' => 'bad-url.pdf',
    'fullscreen' => 'false',
    'download' => 'false',
    'print' => 'true',
    'openfile' => 'false',
    'iframe' => 'false',
    'embed' => 'true',
    'share' => 'false'
  ), $args);
  //run function that actually does the work of the plugin
  $pdfjs_output = pdfjs_function($args);
  //send back text to replace shortcode in post
  return $pdfjs_output;
}

function pdfjs_function($incomingfromhandler)
{
    $plugin_url = home_url().'/wp-content/plugins/wpdkaprogramlistings/';

  // $viewer_base_url= $siteURL."/wp-content/plugins/wpdkaprogramlistings/pdfjs/web/viewer.php";

    $viewer_base_url = plugins_url('web/viewer.php', __FILE__);

    $file_name = $incomingfromhandler['url'];
    $fullscreen = $incomingfromhandler['fullscreen'];
    $download = $incomingfromhandler['download'];
    $print = $incomingfromhandler['print'];
    $openfile = $incomingfromhandler['openfile'];
    $iframe = $incomingfromhandler['iframe'];
    $embed = $incomingfromhandler['embed'];
    $share = $incomingfromhandler['share'];

    if ($download != 'true') {
        $download = 'false';
    }

    if ($print != 'true') {
        $print = 'false';
    }

    if ($openfile != 'true') {
        $openfile = 'false';
    }
    $re = '/.*danskkulturarv.dk\\/.*/';

    if (!preg_match($re, $file_name, $matches)) {
        return 'Not valid url';
    }

    $final_url = $viewer_base_url.'?file='.$file_name.'&download='.$download.'&print='.$print.'&openfile='.$openfile;
    $root_url = (!empty($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/';
    $page_url =  "//{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
    $parsed = parse_url($page_url);
    $path = $parsed['path'];
    $path_parts = explode('/', $path);
    $whitepage_slug = $path_parts[1];
    $short_name = preg_replace('/http\:\/\/files\.danskkulturarv\.dk\//', '', $file_name);
    $shorter_name = preg_replace('/\.pdf/', '', $short_name);
    $image_url = $plugin_url.'image-print/pdftopng.php?pdf='.$shorter_name;
    $card_url = $plugin_url.'image-print/?type=card&pdf='.$shorter_name;
    $poster_url = $plugin_url.'image-print/?type=poster&pdf='.$shorter_name;
    $buttons_start = '<div class="btn-group" role="group" aria-label="Do more">';
    $buttons_end = '</div> ';
    $button_class = 'type="button" class="btn btn-default margin-right"';
    $button_ok_class = 'type="button" class="btn btn-default modal-ok"';
    $dropdown_start = '<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">';
    $dropdown_end = ' <span class="caret"></span></button>';

    $postcard_link = '';
    if ($print == 'true' && is_user_logged_in()) {
        $postcard_link = '<a '.$button_class.' target="_blank" href="'.$card_url.'">'.__('Create postcard', wpdkaprogramlistings::DOMAIN).'</a> ';
    }

    $poster_link = '';
    if ($print == 'true' && is_user_logged_in()) {
        $poster_link = '<a '.$button_class.' target="_blank" href="'.$poster_url.'">'.__('Create poster', wpdkaprogramlistings::DOMAIN).'</a>';

    }

    $share_page = '';
    if ($share == 'true') {
      $share_page = '<a '.$button_class.' href="'.$root_url.$whitepage_slug.'?pdf='.$shorter_name.'">Preview + del</a>';
    }

    $pull_right = '';
    if ($iframe == 'false') {
      $pull_right = 'pull-right';
    }

    $downloads = '';
    if ($download == 'true') {
        $downloads = $buttons_start.$dropdown_start.__('Download', wpdkaprogramlistings::DOMAIN).$dropdown_end.'<ul class="dropdown-menu '.$pull_right.'">
           <li><a href="'.$file_name.' "target="_blank">'.__('Document (PDF)', wpdkaprogramlistings::DOMAIN).'</a></li>
           <li><a href="#" data-toggle="modal" data-target="#pdf'.$shorter_name.'">'.__('Convert to image (JPG)', wpdkaprogramlistings::DOMAIN).'</a></li>
         </ul>
         <div class="modal" id="pdf'.$shorter_name.'" tabindex="-1" role="dialog">
           <div class="modal-dialog modal-sm">
             <div class="modal-content">
                <div class="modal-body text-left">
                  '.__('Converting to JPG takes ~15 seconds.<br/>Click OK to begin.', wpdkaprogramlistings::DOMAIN).'
                </div>
                <div class="modal-footer">
                  <button data-dismiss="modal" '.$button_class.'>'.__('Cancel', wpdkaprogramlistings::DOMAIN).'</button>
                  <a '.$button_ok_class.' target="_blank" href="'.$image_url.'">'.__('OK', wpdkaprogramlistings::DOMAIN).'</a>
                </div>
             </div>
           </div>
         </div>'.$buttons_end;
    }

    $embed_code = '';
    if ($embed == 'true') {
        $embed_code = '<embed src="'.$file_name.'" class="embed-pdf" type="application/pdf">';
    }

    $iframe_code = '';
    if ($iframe == 'true') {
        $iframe_code = '<div class="a4-wrap"><iframe class="pdf-viewer" src="'.$final_url.'"></iframe></div>';
    }

    return $iframe_code.$share_page.$downloads.$postcard_link.$poster_link;
}
