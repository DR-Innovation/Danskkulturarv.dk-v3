<?php

// Uncomment error reporting to see php errors

// error_reporting(E_ALL);
// ini_set('display_errors', '1');

if ($_GET['pdf']) {
    $pdf = $_GET['pdf'];
}
if ($_GET['base']) {
    $base = $_GET['base'];
}

?>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title>Postkort Dansk Kulturarv</title>

  <!-- Latest compiled and minified CSS -->
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
  <link href='https://fonts.googleapis.com/css?family=Montserrat:400,700' rel='stylesheet' type='text/css'>
  <link href="bower_components/cropper/dist/cropper.css" rel="stylesheet" type="text/css">
  <link href="styles/styles.css" rel="stylesheet" type="text/css">


  <meta property="og:url"                content="http://test.danskkulturarv.dk/wp-content/plugins/wpdkaprogramlistings/image-print/postcard.php?base=" />
  <meta property="og:type"               content="website" />
  <meta property="og:title"              content="Dansk Kulturarv" />
  <meta property="og:description"        content="Lav postkort og plakater fra gamle sendeplaner." />
  <meta property="og:image" content="images/postkortogplakater.jpg" />

  <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
  <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
  <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>

<body>
  <h1 class="noprint">Lav postkort</h1>
  <div id="card-crop">
    <img src="images/pdftopng.jpeg" />
    <!-- <img class="img" src="pdftopng.php?pdf=<?php echo $pdf ?>" /> -->
  </div>

  <div class="row noprint">
    <div class="col-xs-12">
      <h2>Print</h2>
      <ul>
        <li>
          Print af postkort virker bedst i <a href="http://www.google.com/chrome/" target="_blank">Google Chrome</a> (gratis).
        </li>
        <li>
          Standardformatet er A4 stående eller A5 liggende.
        </li>
        <li>
          Ved andre størrelser får du pæneste resultat ved at gemme som pdf først.
        </li>
        <li>
          Du kan gemme som pdf i Chrome ved at vælge "Gem som pdf" som printer.
        </li>
      </ul>
    </div>
    <div class="col-xs-12 text-center loadhide">
      <button onclick="window.print()" type="button" class="btn btn-primary" title="print"><i class="fa fa-print"></i> Print</button>
    </div>
  </div>
  <div class="row noprint">
    <div class="col-xs-12">
      <h2>Del</h2>
      <ul>
        <li>
          Du kan også dele postkortet (forsiden) på sociale medier
        </li>
        <li>
          Rotér, zoom og juster billedet som ønsket og tryk herefter gem inden du deler på fx facebook.
        </li>
      </ul>
    </div>
    <div class="col-xs-12 text-center loadhide">
      <button id="pngButton" type="button" class="btn btn-primary" title="Save png"><i class="fa fa-floppy-o"></i> Gem</button>
      <div class="btn-group">
        <a href="http://www.facebook.com/sharer.php?u=http://www.danskkulturarv.dk/programoversigt/" id="fbButton" type="button" class="btn btn-primary" title="Save png"><i class="fa fa-facebook"></i></a>
        <button id="twButton" type="button" class="btn btn-primary" title="Save png"><i class="fa fa-twitter"></i></button>
        <button id="inButton" type="button" class="btn btn-primary" title="Save png"><i class="fa fa-instagram"></i></button>
      </div>
    </div>
  </div>


  <h2 class="noprint">Forside</h2>

  <div id="adjustment" class="noprint loadhide">
    <div class="btn-group">
      <button type="button" class="btn btn-primary btn-lg" data-method="rotate" title="Rotate Left"><i class="fa fa-rotate-left"></i></button>
      <button type="button" class="btn btn-primary btn-lg" data-method="rotate" title="Rotate Right"><i class="fa fa-rotate-right"></i></button>
    </div>
    <div class="btn-group">
      <button type="button" class="btn btn-primary btn-lg" data-method="zoom" title="Zoom In"><i class="fa fa-search-plus"></i></button>
      <button type="button" class="btn btn-primary btn-lg" data-method="zoom" title="Zoom Out"><i class="fa fa-search-minus"></i></button>
    </div>
    <div class="btn-group">
      <button type="button" class="btn btn-primary btn-lg" data-method="move" title="Move Left"><i class="fa fa-arrow-left"></i></button>
      <button type="button" class="btn btn-primary btn-lg" data-method="move" title="Move Right"><i class="fa fa-arrow-right"></i></button>
      <button type="button" class="btn btn-primary btn-lg" data-method="move" title="Move Up"><i class="fa fa-arrow-up"></i></button>
      <button type="button" class="btn btn-primary btn-lg" data-method="move" title="Move Down"><i class="fa fa-arrow-down"></i></button>
    </div>
  </div>

  <div class="postcard-wrap" id="frontpage">
    <div class="postcard-image">
      <div id="front"></div>
      <div id="load">Konverterer billede</div>
    </div>
    <div class="front-text">&copy; Danmarks Radio. <b>Danskkulturarv.dk</b></div>
  </div>


  <h2 class="noprint">Bagside</h2>

  <div class="postcard-wrap back">
    <div class="postcard-image backside">
      <div class="postcard-text">
        <textarea class="greeting" placeholder="Indtast evt. hilsen her"></textarea>
        <textarea class="address" placeholder="Indtast evt. adresse her"></textarea>
      </div>
      <img src="images/postcardBG.svg" />
    </div>
  </div>


  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
  <script src="bower_components/cropper/dist/cropper.min.js"></script>
  <script src="bower_components/html2canvas/build/html2canvas.js"></script>
  <script src="scripts/postcard.js"></script>
  <script src="scripts/canvas2image.js"></script>
</body>

</html>
