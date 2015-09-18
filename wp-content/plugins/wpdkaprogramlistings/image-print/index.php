<?php

// Uncomment error reporting to see php errors

// error_reporting(E_ALL);
// ini_set('display_errors', '1');

if ($_GET['pdf']) {
    $pdf = $_GET['pdf'];
    $year = substr($pdf, 2, 4);
    $month = substr($pdf, 7, 2);
    $day = substr($pdf, 10, 2);
    $validDate = false;
    if (substr($day, 0, 1) == "0") { $day = substr($day, 1, 1); }
    if ($month == "01") { $month = "Januar"; }
    else if ($month == "02") { $month = "Februar"; }
    else if ($month == "03") { $month = "Marts"; }
    else if ($month == "04") { $month = "April"; }
    else if ($month == "05") { $month = "Maj"; }
    else if ($month == "06") { $month = "Juni"; }
    else if ($month == "07") { $month = "Juli"; }
    else if ($month == "08") { $month = "August"; }
    else if ($month == "09") { $month = "September"; }
    else if ($month == "10") { $month = "Oktober"; }
    else if ($month == "11") { $month = "November"; }
    else if ($month == "12") { $month = "December"; }
    if (strlen($month) >= 3) { $validDate = true; }
}
if ($_GET['base']) {
    $base = $_GET['base'];
}
if ($_GET['type']) {
  $type = $_GET['type'];
}

$buttonclass = 'type="button" class="btn btn-default btn-lg disabled"';

$adjustment = '
<div id="adjustment" class="noprint">
  <div class="btn-group">
    <button ' . $buttonclass . ' data-method="rotate" title="Rotate Left"><i class="fa fa-rotate-left"></i></button>
    <button ' . $buttonclass . ' data-method="rotate" title="Rotate Right"><i class="fa fa-rotate-right"></i></button>
  </div>
  <div class="btn-group">
    <button ' . $buttonclass . ' data-method="zoom" title="Zoom In"><i class="fa fa-search-plus"></i></button>
    <button ' . $buttonclass . ' data-method="zoom" title="Zoom Out"><i class="fa fa-search-minus"></i></button>
  </div>
  <div class="btn-group">
    <button ' . $buttonclass . ' data-method="move" title="Move Left"><i class="fa fa-arrow-left"></i></button>
    <button ' . $buttonclass . ' data-method="move" title="Move Right"><i class="fa fa-arrow-right"></i></button>
    <button ' . $buttonclass . ' data-method="move" title="Move Up"><i class="fa fa-arrow-up"></i></button>
    <button ' . $buttonclass . ' data-method="move" title="Move Down"><i class="fa fa-arrow-down"></i></button>
  </div>
</div>
';
?>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=834, user-scalable=0"/>


  <?php if ($type == 'card') : ?>
  <title>Postkort Dansk Kulturarv</title>
  <?php elseif ($type == 'poster') : ?>
  <title>Plakat Dansk Kulturarv</title>
  <?php endif; ?>

  <!-- Latest compiled and minified CSS -->
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
  <link href='https://fonts.googleapis.com/css?family=Montserrat:400,700' rel='stylesheet' type='text/css'>
  <link href="bower_components/cropper/dist/cropper.css" rel="stylesheet" type="text/css">
  <link href="styles/styles.css" rel="stylesheet" type="text/css">

  <meta property="og:url"         content="" />
  <meta property="og:type"        content="website" />
  <meta property="og:title"       content="Dansk Kulturarv" />
  <meta property="og:description" content="Lav postkort og plakater fra gamle sendeplaner." />
  <meta property="og:image"       content="images/postkortogplakater.jpg" />

  <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
  <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
  <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>

<body>

  <div id="card-crop">
    <!-- <img src="images/pdftopng.jpeg" /> -->
    <img class="img" src="pdftopng.php?pdf=<?php echo $pdf ?>" />
  </div>


  <?php if ($type == 'card') : ?>
  <h1 class="noprint">Postkort</h1>


  <div class="row noprint">
    <div class="col-xs-12">
      <p>Brug knapperne til at rotére, zoome og justere billedet.</p>
    </div>
  </div>


  <?php echo $adjustment; ?>


  <div class="postcard-wrap" id="frontpage">
    <div class="postcard-image">
      <div id="front"></div>
      <div id="load">Konverterer pdf til billede (~15 sek) </div>
    </div>
    <div class="front-text left">
      <b>
        <?php if ($validDate == true){ echo 'DR sendeplan ' . $day . '. ' . $month . ' ' . $year . ' &copy; DR'; } ?>
      </b>
    </div>
    <div class="front-text right"><b>Danskkulturarv.dk</b></div>
  </div>


  <p class="noprint">Klik på den grå tekst for at indtaste eventuel hilsen og adresse.</p>


  <div class="postcard-wrap back">
    <div class="postcard-image backside">
      <div class="postcard-text">
        <textarea class="greeting" placeholder="Indtast evt. hilsen her"></textarea>
        <textarea class="address" placeholder="Indtast evt. adresse her"></textarea>
      </div>
      <img src="images/postcardBG.svg" />
    </div>
  </div>


  <div class="row noprint">
    <div class="col-xs-12">
      <h2>Print/gem som pdf</h2>
      <ul>
        <li>Virker bedst i <a href="http://www.google.com/chrome/" target="_blank">Google Chrome</a>.</li>
        <li>Understøtter formaterne A4 stående eller A5 liggende (to sider).</li>
        <li>Andre formater: gem som pdf først og åbn herefter pdf på din computer.</li>
        <li>Gem som pdf i Chrome ved at trykke "print" og vælg "Gem som pdf" som printer.</li>
      </ul>
    </div>
    <div class="col-xs-12 text-center">
      <button onclick="window.print()" type="button" class="btn btn-primary btn-lg disabled" title="print"><i class="fa fa-print"></i> Print</button>
    </div>
  </div>


  <div class="row noprint last-one">
    <div class="col-xs-12">
      <h2>Gem forside som billede</h2>
      <ul>
        <li>Lavere opløsning til deling fx på sociale medier (kan i nogle browsere tage et par sek).</li>
      </ul>
    </div>
    <div class="col-xs-12 text-center">
      <button id="pngButton" type="button" class="btn btn-primary btn-lg disabled" title="Save png"><i class="fa fa-download"></i> Download</button>
    </div>
  </div>


<?php elseif ($type == 'poster') : ?>


  <h1 class="noprint">Plakat</h1>

  <div class="row noprint">
    <div class="col-xs-12">
      <p>Brug knapperne til at rotére, zoome og justere billedet.</p>
    </div>
  </div>

  <?php echo $adjustment; ?>

  <div class="postcard-wrap poster" id="frontpage">
    <div class="postcard-image">
      <div id="front"></div>
      <div id="load">Konverterer pdf til billede (~15 sek) </div>
    </div>
    <div class="front-text left">
      <b>
        <?php if ($validDate == true){ echo 'DR sendeplan ' . $day . '. ' . $month . ' ' . $year . ' &copy; DR'; } ?>
      </b>
    </div>
    <div class="front-text right"><b>Danskkulturarv.dk</b></div>
  </div>

  <div class="row noprint last-one">
    <div class="col-xs-12">
      <h2>Print/gem som pdf</h2>
      <ul>
        <li>Virker bedst i <a href="http://www.google.com/chrome/" target="_blank">Google Chrome</a>.</li>
        <li>Understøtter formaterne A4 stående eller A5 liggende (to sider).</li>
        <li>Andre formater: gem som pdf først og åbn herefter pdf på din computer.</li>
        <li>Gem som pdf i Chrome ved at trykke "print" og vælg "Gem som pdf" som printer.</li>
      </ul>
    </div>
    <div class="col-xs-12 text-center">
      <button onclick="window.print()" type="button" class="btn btn-primary btn-lg disabled" title="print"><i class="fa fa-print"></i> Print</button>
    </div>
  </div>

<?php endif; ?>

  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
  <script src="bower_components/cropper/dist/cropper.min.js"></script>
  <script src="bower_components/html2canvas/build/html2canvas.js"></script>
  <script src="scripts/download.js"></script>
  <script src="scripts/postcard.js"></script>

<?php if ($type == 'card') : ?>

  <script>
  $(window).load(function() {
  // Initialize cropper
  //   width: 794px;
  //   height: 559px;


    $('#card-crop > img').cropper({
      aspectRatio: 694 / 459,
      autoCropArea: 1,
      guides: false,
      highlight: false,
      cropBoxResizable: false,
      preview: "#front"
    });
  });
  </script>

<?php elseif ($type == 'poster') : ?>

  <script>
  $(window).load(function() {
  // Initialize cropper
  //     height: 1126px;

    $('#card-crop > img').cropper({
      aspectRatio: 694 / 1026,
      autoCropArea: 1,
      guides: false,
      highlight: false,
      cropBoxResizable: false,
      preview: "#front"
    });
  });
  </script>

<?php endif; ?>

</body>

</html>
