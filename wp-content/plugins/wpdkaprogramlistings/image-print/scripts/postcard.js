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

  $('#adjustment').on('touchstart click', function() {
    $('#downloadButton').attr( "disabled", "disabled" );
  });

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


}); // END DOCUMENT READY


$(window).load(function() {


  // Showing/hiding stuff on window load
  $("body").addClass('loaded');


  // html2canvas
  $("#pngButton").click(function() {
    html2canvas($("#frontpage"), {
      onrendered: function(canvas) {

        // convert canvas to base64 and store as variable img
        var img = canvas.toDataURL("image/png");

        // facebook url
        var url = "http://danskkulturarv.dk/wp-content/plugins/wpdkaprogramlistings/image-print/?base=" + img;
        // facebook sharer url
        var fburl = "http://www.facebook.com/sharer.php?u=" + url;

        $('#downloadButton').attr("href", img).removeAttr("disabled");

        // update metadata
        // $('meta[property="og:image"]').attr("content", img);
        // $('meta[property="og:url"]').attr("content", url);
        // $('#fbButton').attr("href", fburl);

        // $('body').append('<img src="' + img + '"/>');
        // Save PNG
        // return Canvas2Image.saveAsPNG(canvas);
      }
    });


  });
});

/*
http://stackoverflow.com/questions/21111893/upload-base64-image-facebook-graph-api-how-to-use-this-script/21145106#21145106
*/
