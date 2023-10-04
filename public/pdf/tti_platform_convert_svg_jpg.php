<!-- File to convert SVG to JPG -->
<!-- Contains script to convert SVG to JPG  -->
<html>
<head>
<title>TTI Insights</title>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
<style>

body {
  background: #1b3a63;
  text-align: center;
  overflow: hidden; /* Hide scrollbars */
}

.loader {
  width: 410px;
  height: 50px;
  line-height: 50px;
  text-align: center;
  position: absolute;
  font-size: 20px;
  top: 50%;
  left: 50%;
  -webkit-transform: translate(-50%, -50%);
          transform: translate(-50%, -50%);
  font-family: helvetica, arial, sans-serif;
  text-transform: uppercase;
  font-weight: 900;
  color: #fff;
  letter-spacing: 0.2em;
}
.loader::before, .loader::after {
  content: "";
  display: block;
  width: 12px;
  height: 12px;
  background: #fff;
  position: absolute;
  -webkit-animation: load .7s infinite alternate ease-in-out;
          animation: load .7s infinite alternate ease-in-out;
}
.loader::before {
  top: 0;
}
.loader::after {
  bottom: 0;
}

@-webkit-keyframes load {
  0% {
    left: 0;
    height: 30px;
    width: 15px;
  }
  50% {
    height: 8px;
    width: 40px;
  }
  100% {
    left: 3px;
    height: 30px;
    width: 15px;
  }
}

@keyframes load {
  0% {
    left: 0;
    height: 30px;
    width: 15px;
  }
  50% {
    height: 8px;
    width: 40px;
  }
  100% {
    left: 425px;
    height: 30px;
    width: 15px;
  }
}


</style>
</head>
<body bgcolor="#E6E6FA">
 <?php

$svg_file_link = $_GET['svg_url'];
$key_name = $_GET['key_name'];
$assess_id = $_GET['assess_id'];
$report_type = $_GET['report_type'];
$user_id = $_GET['user_id'];

$svg_file = file_get_contents($svg_file_link);

$find_string   = '<svg';
$position = strpos($svg_file, $find_string);

$svg_file_new = substr($svg_file, $position);

// echo "<div style='width:100%; height:100%;' >" . $svg_file_new . "</div>";
 ?>
<!--<img src="https://www.ministryinsights.com/wp-content/uploads/2020/04/Wordmark_-_Primary.png" style="text-align:center;margin-top:20px;width: 300px;" />-->

<div class="loader" ><span style="position: relative;left: 14px;">...Creating PDF Report...</span></div>



 <textarea id="t" rows="8" cols="70" style="display:none;" value="<?php echo $svg_file; ?>"></textarea>
 <!--<button id="l">Load SVG</button><br/><br/>-->
 <div style="visibility: hidden;" id="d"></div><br/>
<input style="visibility: hidden;" id="w" type="number" max="9999"></input>
<input style="visibility: hidden;" id="h" type="number" max="9999"></input>
 <!--<button id="s">Save SVG as PNG</button>-->
 <canvas style="visibility: hidden;" id="c"></canvas>
<script>
/* SVG to PNG (c) 2017 CY Wong / myByways.com */
var text = document.getElementById('t');
text.wrap = 'off';
var svg = null;
var width = document.getElementById('w');
var height = document.getElementById('h'); 
var dddd = document.getElementById('l');



  var div = document.getElementById('d');
  div.innerHTML= text.value;
  svg = div.querySelector('svg');
  width.value = svg.getBoundingClientRect().width;
  height.value = svg.getBoundingClientRect().height;


  var canvas = document.getElementById('c');
  svg.setAttribute('width', width.value);
  svg.setAttribute('height', height.value);
  canvas.width = width.value;
  canvas.height = height.value;
  var data = new XMLSerializer().serializeToString(svg);
  var win = window.URL || window.webkitURL || window;
  var img = new Image();
  var blob = new Blob([data], { type: 'image/svg+xml' });
  var url = win.createObjectURL(blob);
  img.onload = function () {
    canvas.getContext('2d').drawImage(img, 0, 0);
    win.revokeObjectURL(url);
    var uri = canvas.toDataURL('image/png');
    
    var a = document.createElement('a');
    document.body.appendChild(a);
    a.style = 'display: none';
    a.href = uri
    a.download = (svg.id || svg.getAttribute('name') || svg.getAttribute('aria-label') || 'wheel_chart') + '.png';
    //a.click();
   loads_the_img(uri, url);
    window.URL.revokeObjectURL(uri);
    
    //document.body.removeChild(a);
  };
 
  img.src = url;



function loads_the_img(uri,url) {
  //console.log(uri);
  $.ajax({
      type: "POST",
      url: 'tti_platform_convert_svg_ajax.php',
      data: {
        uri: uri,
        url: url,
        keyname: <?php echo $key_name; ?>
      },
      success: function(data){
         $('.loader span').text('...Downloading PDF...');
          <?php if( $user_id ): ?>
            window.location= window.location.origin+"?report_type=<?php echo $report_type; ?>&user_id=<?php echo $user_id; ?>&assess_id=<?php echo $assess_id; ?>&tti_print_consolidation_report=1&keyname=<?php echo $key_name; ?>";
          <?php else: ?>
            window.location= window.location.origin+"?report_type=<?php echo $report_type; ?>&assess_id=<?php echo $assess_id; ?>&tti_print_consolidation_report=1&keyname=<?php echo $key_name; ?>";
          <?php endif; ?>

         setInterval(function(){
             $('.loader span').text('...Download Complete...');
            
             
         },3000);
         
         setInterval(function(){
             //window.location="https://dev.ministryinsights.com/courses/report-consolidation-leading-from-your-strengths-team-building-activity/lessons/consolidation-team-building-exercise/";
              window.close();
             
         },5000);

      }
  });
}


</script>

<?php



?>


</body>
</html>

