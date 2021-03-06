/**********
 * Variables
 **********/

var backImg = $('.backside').find('img').attr("src");
var loadText = $("#load").text();
var round = 0;
var docDefinition;
var croppedImage = $('#card-crop > img');

$("#pdfInProgress").hide();
$("#pngInProgress").hide();

/***********
 * Functions
 ***********/

// "Loading ..."
setInterval(function() {
  $("#load").append(".");
  round++;
  if (round == 4) {
    $("#load").html(loadText);
    round = 0;
  }
}, 400);

function pdfAlert() {
  $("#pdfInProgress").show();
  analyticsPdfEvent();
}

function pngAlert() {
  $("#pngInProgress").show();
  analyticsImageEvent();
}

function postcardContent(resolution) {
  var deferred = $.Deferred();
  docDefinition = {
    // a string or { width: number, height: number }
    pageSize: 'A5',
    // by default we use portrait, you can change it to landscape if you wish
    pageOrientation: 'landscape',
    // [left, top, right, bottom] or [horizontal, vertical] or just a number for equal margins
    pageMargins: [15, 15, 15, 0],
    content: [{
      image: '',
      width: 535,
      margin: [15, 17, 0, 10],
    }, {
      columns: [{
        alignment: 'left',
        style: 'bottomStyle',
        text: ''
      }, {
        alignment: 'right',
        style: 'bottomStyle',
        text: '',
      }]
    }, {
      image: '',
      pageBreak: 'before',
      width: 535,
      margin: [15, 17, 0, 10],
    }, {
      columns: [{
        width: 344,
        style: 'textStyle',
        margin: [12, -364, 40, 0],
        lineHeight: 1.22,
        text: '',
      }, {
        style: 'textStyle',
        margin: [20, -206, 0, 0],
        lineHeight: 2.11,
        text: '',
      }]
    }, ],
    styles: {
      bottomStyle: {
        fontSize: 10,
        bold: true,
      },
      textStyle: {
        fontSize: 16,
      }
    }
  };
  docDefinition.content[0].image = croppedImage.cropper('getCroppedCanvas', {
    width: resolution
  }).toDataURL();
  docDefinition.content[1].columns[0].text = $('.front-text.left b').text();
  docDefinition.content[1].columns[1].text = "Danskkulturarv.dk";
  docDefinition.content[3].columns[0].text = $('.greeting').val();
  docDefinition.content[3].columns[1].text = $('.address').val();
  docDefinition.content[2].image = backImg;
  deferred.resolve();
  return deferred.promise();
}

function posterContent(resolution) {
  var deferred = $.Deferred();
  docDefinition = {
    pageSize: 'A4',
    pageOrientation: 'portrait',
    pageMargins: [15, 15, 15, 0],
    content: [{
      image: '',
      width: 535,
      margin: [15, 17, 0, 10],
    }, {
      columns: [{
        alignment: 'left',
        style: 'bottomStyle',
        text: ''
      }, {
        alignment: 'right',
        style: 'bottomStyle',
        text: '',
      }]
    }, ],
    styles: {
      bottomStyle: {
        fontSize: 10,
        bold: true,
      },
      textStyle: {
        fontSize: 16,
      }
    }
  };
  docDefinition.content[0].image = croppedImage.cropper('getCroppedCanvas', {
    width: resolution
  }).toDataURL();
  docDefinition.content[1].columns[0].text = $('.front-text.left b').text();
  docDefinition.content[1].columns[1].text = "Danskkulturarv.dk";
  deferred.resolve();
  return deferred.promise();
}


function createThePdf(name) {
  var deferred = $.Deferred();
  $("#pdfInProgress").delay(4000).fadeOut(1000);
  pdfMake.createPdf(docDefinition).download(name);
  docDefinition = '';
  deferred.resolve();
  return deferred.promise();
}


function createPng(id, name) {
  $("#pngInProgress").delay(1000).fadeOut(1000);
  html2canvas($(id), {
    onrendered: function(canvas) {
      // convert canvas to base64 and store as variable img
      var img = canvas.toDataURL("image/png");
      // Safari and iOS devices doesn't support renaming files on download
      if (navigator.userAgent.match(/(iPod|iPhone|iPad)/)) {
        window.location.assign(img);
      } else if (navigator.userAgent.indexOf('Safari') != -1 && navigator.userAgent
        .indexOf('Chrome') == -1) {
        window.location.assign(img);
      } else {
        download(img, name, "image/png");
      }
    }
  })
}

function analyticsPdfEvent() {
  ga('send', {
    hitType: 'event',
    eventCategory: 'Buttons',
    eventAction: 'save',
    eventLabel: 'Pdf'
  });
}

function analyticsImageEvent() {
  ga('send', {
    hitType: 'event',
    eventCategory: 'Buttons',
    eventAction: 'save',
    eventLabel: 'Image'
  });
}

/********************************************************
 * Don't do this stuff until the image conversion is done
 ********************************************************/
$(window).load(function() {

  // Initialise cropper for postcard
  $('#card-crop.card > img').cropper({
    aspectRatio: 694 / 459,
    autoCropArea: 1,
    guides: false,
    highlight: false,
    cropBoxResizable: false,
    preview: "#front"
  });

  // Initialise cropper for poster
  $('#card-crop.poster > img').cropper({
    aspectRatio: 694 / 1010,
    autoCropArea: 1,
    guides: false,
    highlight: false,
    cropBoxResizable: false,
    preview: "#front"
  });

  // Showing/hiding stuff on window load
  $("body").addClass('loaded');
  $("button").removeClass('disabled');


  // Create Postcard
  $('#pdfPostcardButton').on('touchstart click', function() {
    $.when($.ajax(pdfAlert())).then(function() {
      postcardContent(1500).then(createThePdf('postkort.pdf'));
    });
  });
  $('#pdfHighPostcardButton').on('touchstart click', function() {
    $.when($.ajax(pdfAlert())).then(function() {
      postcardContent(5000).then(createThePdf('postkort.pdf'));
    });
  });


  // Create Poster
  $('#pdfPosterButton').on('touchstart click', function() {
    $.when($.ajax(pdfAlert())).then(function() {
      posterContent(2000).then(createThePdf('plakat.pdf'));
    });
  });
  $('#pdfHighPosterButton').on('touchstart click', function() {
    $.when($.ajax(pdfAlert())).then(function() {
      posterContent(5000).then(createThePdf('plakat.pdf'));
    });
  });


  // Create frontpage image
  $("#pngPostcardButton").click(function() {
    $.when($.ajax(pngAlert())).then(function() {
      createPng("#frontpage", "postkort-forside.png");
    });
  });
  $("#pngPosterButton").click(function() {
    $.when($.ajax(pngAlert())).then(function() {
      createPng("#frontpage", "plakat.png");
    });
  });


  // Create backpage image
  $("#backpngButton").click(function() {
    if ($('.greeting').val() === "") {
      $('.greeting').val(" ");
    }
    if ($('.address').val() === "") {
      $('.address').val(" ");
    }
    analyticsImageEvent();
    createPng("#backpage", "postkort-bagside.png");
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


});
