<!doctype html>
<html lang=en>
<head>
<style>

body {
}

input[type=text] {
    width: 400px;
}

.hidden {
    display: none;
}

#wrap {
    position: relative;
    overflow: visible;

}

.stack {
    position: absolute;
    left: 0;
    top: 0;
}

.area {
    float: left;
    padding: 1em;
    background-color: #fff;
}

.top {
    z-index: 10;
}

</style>
</head>

<body>
<h1>Compare images</h1>
<p>Add link to images and visually compare them. Change the link och press return to load the image. <a href="http://dbwebb.se/opensource/cimage">Read more...</a></p>

<form>
    <p>
        <label>Image 1: <input type="text" id="input1" data-id="1"></label> <img id="thumb1"></br>
        <label>Image 2: <input type="text" id="input2" data-id="2"></label> <img id="thumb2"></br>
        <label>Image 3: <input type="text" id="input3" data-id="3"></label> <img id="thumb3"></br>
        <label>Image 4: <input type="text" id="input4" data-id="4"></label> <img id="thumb4"></br>
        <label><input type="checkbox" id="viewDetails">Hide image details?</label><br/>
        <label><input type="checkbox" id="stack">Stack images?</label>
    </p>
</form>

<div id="buttonWrap" class="hidden">
    <button id="button1" class="button" data-id="1">Image 1</button>
    <button id="button2" class="button" data-id="2">Image 2</button>
    <button id="button3" class="button" data-id="3">Image 3</button>
    <button id="button4" class="button" data-id="4">Image 4</button>
</div>

<div id="wrap">

    <div id="area1" class="area">
        <code>Image 1</code><br>
        <img id="img1">
        <pre id="json1" class="json"></pre>
    </div>

    <div id="area2" class="area">
        <code>Image 2</code><br>
        <img id="img2">
        <pre id="json2" class="json"></pre>
    </div>

    <div id="area3" class="area">
        <code>Image 3</code><br>
        <img id="img3">
        <pre id="json3" class="json"></pre>
    </div>

    <div id="area4" class="area">
        <code>Image 4</code><br>
        <img id="img4">
        <pre id="json4" class="json"></pre>
    </div>

</div>


</body>

<script src="https://code.jquery.com/jquery-2.1.1.min.js"></script>
<script src="../js/cimage.js"></script>
<script>
<?php 
if (isset($script)) {
    echo $script; 
} else {
    echo "CImage.compare({});";
} ?>
</script>

</html>
