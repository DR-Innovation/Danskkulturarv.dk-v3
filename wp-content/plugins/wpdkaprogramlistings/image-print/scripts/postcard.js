$(document).ready(function() {


  // "Loading ..." text
  var originalText = $("#load").text(),
    i = 0;
  setInterval(function() {
    $("#load").append(".");
    i++;
    if (i == 4) {
      $("#load").html(originalText);
      i = 0;
    }
  }, 400);


  // Cropper buttons
  $('button[title="Rotate Left"]').on('touchstart click', function() {
    $('#card-crop > img').cropper('rotate', -90);
  });
  $('button[title="Rotate Right"]').on('touchstart click', function() {
    $('#card-crop > img').cropper('rotate', 90);
  });
  $('button[title="Zoom In"]').on('touchstart click', function() {
    $('#card-crop > img').cropper('zoom', 0.1);
  });
  $('button[title="Zoom Out"]').on('touchstart click', function() {
    $('#card-crop > img').cropper('zoom', -0.1);
  });
  $('button[title="Move Left"]').on('touchstart click', function() {
    $('#card-crop > img').cropper('move', 2, 0);
  });
  $('button[title="Move Right"]').on('touchstart click', function() {
    $('#card-crop > img').cropper('move', -2, 0);
  });
  $('button[title="Move Down"]').on('touchstart click', function() {
    $('#card-crop > img').cropper('move', 0, -2);
  });
  $('button[title="Move Up"]').on('touchstart click', function() {
    $('#card-crop > img').cropper('move', 0, 2);
  });
});


$(window).load(function() {


  // Showing/hiding stuff on window load
  $("body").addClass('loaded');


  // Initialize cropper
  $('#card-crop > img').cropper({
    aspectRatio: 3 / 2,
    autoCropArea: 1,
    guides: false,
    highlight: false,
    cropBoxResizable: false,
    preview: "#front"
  });


  // html2canvas
  $("#pngButton").click(function() {
    html2canvas($("#frontpage"), {
      onrendered: function(canvas) {

        // convert canvas to base64 and store as variable img
        var img = canvas.toDataURL("image/png");

        // facebook url
        var url = "http://test.danskkulturarv.dk/wp-content/plugins/wpdkaprogramlistings/image-print/postcard.php?base=" + img;
        // facebook sharer url
        var fburl = "http://www.facebook.com/sharer.php?u=" + url;

        // update metadata
        $('meta[property="og:image"]').attr("content", img);
        $('meta[property="og:url"]').attr("content", url);
        $('#fbButton').attr("href", fburl);

        // $('body').append('<img src="' + img + '"/>');
        // Save PNG
        // return Canvas2Image.saveAsPNG(canvas);
      }
    });
  });
});
