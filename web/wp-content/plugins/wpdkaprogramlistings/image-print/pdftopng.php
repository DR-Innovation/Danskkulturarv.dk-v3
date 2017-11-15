<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');
$image = new Imagick();
if ($_GET['pdf']){
    $pdf = $_GET['pdf'];
    $pdf_url = "https://files.danskkulturarv.dk/".$pdf.".pdf";
    // $image->setResolution(400, 400);
    $image->setResolution(400, 400);
    $image->readImage($pdf_url);
    $image->setImageFormat('jpeg');
    $image->setImageCompressionQuality(100);

    header('Content-type: image/'.$image->getImageFormat());
    echo $image;
} else {
  echo "No pdf selected.";
}

?>
