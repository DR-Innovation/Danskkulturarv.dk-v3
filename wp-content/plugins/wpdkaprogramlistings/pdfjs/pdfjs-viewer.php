<?php

//tell wordpress to register the pdfjs-viewer shortcode
add_shortcode('pdfjs-viewer', 'pdfjs_handler');
add_shortcode('no-pdfjs-viewer', 'pdf_handler');

function pdfjs_handler($args)
{
    //set defaults
  $args = shortcode_atts(array(
    'url' => 'bad-url.pdf',
    'viewer_height' => '1360px',
    'viewer_width' => '100%',
    'fullscreen' => 'false',
    'download' => 'true',
    'print' => 'true',
    'openfile' => 'false',
    'iframe' => 'true',
  ), $args);
  //run function that actually does the work of the plugin
  $pdfjs_output = pdfjs_function($args);
  //send back text to replace shortcode in post
  return $pdfjs_output;
}

function pdf_handler($args)
{
    //set defaults
  $args = shortcode_atts(array(
    'url' => 'bad-url.pdf',
    'viewer_height' => '1360px',
    'viewer_width' => '100%',
    'fullscreen' => 'false',
    'download' => 'true',
    'print' => 'true',
    'openfile' => 'false',
    'iframe' => 'false',
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
    $viewer_height = $incomingfromhandler['viewer_height'];
    $viewer_width = $incomingfromhandler['viewer_width'];
    $fullscreen = $incomingfromhandler['fullscreen'];
    $download = $incomingfromhandler['download'];
    $print = $incomingfromhandler['print'];
    $openfile = $incomingfromhandler['openfile'];
    $iframe = $incomingfromhandler['iframe'];

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
    $short_name = preg_replace('/http\:\/\/files\.danskkulturarv\.dk\//', '', $file_name);
    $shorter_name = preg_replace('/\.pdf/', '', $short_name);
    $image_url = $plugin_url.'image-print/pdftopng.php?pdf='.$shorter_name;
    $card_url = $plugin_url.'image-print/?type=card&pdf='.$shorter_name;
    $poster_url = $plugin_url.'image-print/?type=poster&pdf='.$shorter_name;
    $buttons_start = '<div class="btn-group" role="group" aria-label="Do more">';
    $buttons_end = '</div>';
    $button_class = 'type="button" class="btn btn-default"';
    $button_ok_class = 'type="button" class="btn btn-default modal-ok"';
    $dropdown_start = '<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">';
    $dropdown_end = ' <span class="caret"></span></button>';

    $postcard_link = '';
    if ($print == 'true') {
        $postcard_link = '<a '.$button_class.' target="_blank" href="'.$card_url.'">'.__('Create postcard', wpdkaprogramlistings::DOMAIN).'</a> ';
    }

    $poster_link = '';
    if ($print == 'true') {
        $poster_link = '<a '.$button_class.' target="_blank" href="'.$poster_url.'">'.__('Create poster', wpdkaprogramlistings::DOMAIN).'</a> ';

    }

    $fullscreen_link = '';
    if ($fullscreen == 'true') {
        $fullscreen_link = '<a '.$button_class.' target="_blank" href="'.$final_url.'">'.__('View fullscreen', wpdkaprogramlistings::DOMAIN).'</a>  ';
    }

    $downloads = '';
    if ($download == 'true') {
        $downloads = $dropdown_start.__('Download', wpdkaprogramlistings::DOMAIN).$dropdown_end.'<ul class="dropdown-menu">
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
         </div>';
    }

    $iframe_code = '';
    if ($iframe == 'true') {
        $iframe_code = '<iframe width="'.$viewer_width.';" height="'.$viewer_height.';" src="'.$final_url.'"></iframe> ';
    }


    return $postcard_link.$poster_link./*$fullscreen_link.*/$buttons_start.$downloads.$buttons_end.$iframe_code;
}
