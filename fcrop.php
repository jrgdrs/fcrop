#!/usr/bin/env php
<?php

/**
* fcrop - image cropping with focalpoint consideration
*
* usage:
*              fcrop <inputfile> <output_size> <focalpoint> <outputfile>
* example:
*              php fcrop.php input.jpg 320x480 0.4,0.5 output.png
* 
* values:
* inputfile  - filename with local path, inputfile needs accessible
* output_size - pixel width and height for the file to be outputted
* focalpoint - factor x and y defining one point of interest in the picture
*              from top and left (0.5,0.5 would be centered)
* outputfile - filename with local path, directory has to be writeable
*
* dependencies: imagemagick
*
*
* version:     2.0
* author:      fcrop@joerg-drees.de
*
*/

//// Configuration
// location of imagemagick
$im = "magick";

// commandline processing
if( $argc < 5) die( "usage: fcrop <inputfile> <output_size> <focalpoint> <outputfile> \n"
. "  e.g. php fcrop.php input.jpg 320x480 0.4,0.5 output.png \n");

$inputfile = $argv[1];
console_log( "4", "inputfile: $inputfile ");

$output_size = explode("x", $argv[2] );
$output_width  = $output_size[0];
$output_height = $output_size[1];
console_log( "4", "output_width: $output_width ");
console_log( "4", "output_height: $output_height ");

$focalpoint = explode(",", $argv[3]);
$focalpoint_x = $focalpoint[0];
$focalpoint_y = $focalpoint[1];
console_log( "4", "focalpoint_x: $focalpoint_x ");
console_log( "4", "focalpoint_y: $focalpoint_y ");

$outputfile = $argv[4];
console_log( "4", "outputfile: $outputfile ");

// get image geometry of inputfile
$im_check_cmd = "$im identify -ping -format '%w %h' $inputfile";
exec( $im_check_cmd, $array_geo);
console_log( "4", "im_check_cmd: $im_check_cmd ");
$input_size = explode( " ", $array_geo[0]);
$input_width  = $input_size[0];
$input_height = $input_size[1];
console_log( "5", "input_width: $input_width ");
console_log( "5", "input_height: $input_height ");

// calculations
// - focalpoint next to margin
$focalpoint_x_nearest = min( $focalpoint_x, 1 - $focalpoint_x);
$focalpoint_y_nearest = min( $focalpoint_y, 1 - $focalpoint_y);
console_log( "5", "focalpoint_x_nearest: $focalpoint_x_nearest ");
console_log( "5", "focalpoint_y_nearest: $focalpoint_y_nearest ");

// - scaling factor  
$scaling_x = round( $output_width  / 2 / ( $input_width  * $focalpoint_x_nearest ), 6 );
$scaling_y = round( $output_height / 2 / ( $input_height * $focalpoint_y_nearest ), 6 );
console_log( "5", "scaling_x: $scaling_x ");
console_log( "5", "scaling_y: $scaling_y ");
$scaling = max( $scaling_x, $scaling_y ) ;
console_log( "5", "scaling: $scaling ");

// - imagemagick offsets
$im_offset_x = round( $input_width  * $scaling * $focalpoint_x - $output_width  / 2, 0 );
$im_offset_y = round( $input_height * $scaling * $focalpoint_y - $output_height / 2, 0 );
console_log( "5", "im_offset_x: $im_offset_x ");
console_log( "5", "im_offset_y: $im_offset_y ");

// - css offsets
$css_offset_x = round(( $input_width  - $input_width  * $scaling ) / 2 );
$css_offset_y = round(( $input_height - $input_height * $scaling ) / 2 );
console_log( "5", "css_offset_x: $css_offset_x ");
console_log( "5", "css_offset_y: $css_offset_y ");

$css_offset_before_x = ( $im_offset_x + $css_offset_x ) * -1 ;
$css_offset_before_y = ( $im_offset_y + $css_offset_y ) * -1 ;
console_log( "5", "css_offset_before_x: $css_offset_before_x ");
console_log( "5", "css_offset_before_y: $css_offset_before_y ");

$css_offset_after_x = round( $css_offset_before_x / $scaling );
$css_offset_after_y = round( $css_offset_before_y / $scaling );
console_log( "5", "css_offset_after_x: $css_offset_after_x ");
console_log( "5", "css_offset_after_y: $css_offset_after_y ");

// build imagemagick commands
$im_resize = round( $scaling * 100, 4 ) . "%";
console_log( "5", "im_resize: $im_resize ");
$im_region = $output_width . "x" . $output_height;
if( $im_offset_x >= 0 ){ $im_region .= "+"; };
$im_region .= $im_offset_x;
if( $im_offset_y >= 0 ){ $im_region .= "+"; };
$im_region .= $im_offset_y;
console_log( "5", "im_region: $im_region ");

// create outputfiles
$im_crop_cmd = "$im $inputfile -resize $im_resize -crop $im_region $outputfile";
exec( $im_crop_cmd, $array_proc);
console_log( "4", "im_crop_cmd: $im_crop_cmd ");
$demofile="demofile.png"; ////
$im_simulate_cmd = "$im $inputfile -negate -resize $im_resize -region $im_region -negate $demofile";
exec( "$im_simulate_cmd", $array_proc);
console_log( "4", "im_simulate_cmd: $im_simulate_cmd ");

// write files
mkdir( "./fcrop-new");
copy( $inputfile, "./fcrop-new/original.jpg");
copy( $outputfile, "./fcrop-new/hardcrop.png");
copy( $demofile, "./fcrop-new/demo.png");

$fp = fopen( "./fcrop-new/index.html", "w");
$fp_content ="<!DOCTYPE html>
<html xmlns=\"http://www.w3.org/1999/xhtml\">
<head>
    <style>
    
    body { font-family: Sans-Serif; }
    table { width:80%; }
    td { background-color: #cccccc; }

    div {
    width: " . $output_width . "px; height: " . $output_height . "px;
    overflow:hidden;
    }
    img.mx {
    transform: matrix(" . $scaling . ", 0, 0, " . $scaling . ", " . $css_offset_before_x . ", " . $css_offset_before_y . " );
    }
    img.st {
    transform: scale( " . $scaling . ", " . $scaling . ") translate( " . $css_offset_after_x . "px, " . $css_offset_after_y . "px );
    }
    img.ts {
    transform: translate( " . $css_offset_before_x . "px, " . $css_offset_before_y . "px) scale( " . $scaling . ", " . $scaling . " ); 
    }

    </style>
</head>
<body>
	<p><img src=\"original.jpg\" width=\"80%\" /></p>
	<p>Input Size: <br/>
	$im identify -ping -format '%w %h' $inputfile <br/>
	$input_width x $input_height </p>
	
	<table>
	<tr><td>Value</td><td>x/width</td><td>y/height</td></tr>
	<tr><td>Input Size (px)</td><td>$input_width</td><td>$input_height</td></tr>
	<tr><td>Output Size (px)</td><td>$output_width</td><td>$output_height</td></tr>
	<tr><td>Focalpoint (factor)</td><td>$focalpoint_x</td><td>$focalpoint_y</td></tr>
	<tr><td>Focalpoint Nearest (factor)</td><td>$focalpoint_x_nearest</td><td>$focalpoint_y_nearest</td></tr>
	<tr><td>Scaling (factor)</td><td>$scaling_x</td><td>$scaling_y</td></tr>
	<tr><td>Scaling Max (factor)</td><td>$scaling</td><td>$scaling</td></tr>
	<tr><td>IM Offset top/left (px)</td><td>$im_offset_x</td><td>$im_offset_y</td></tr>
	<tr><td>CSS Offset (px)</td><td>$css_offset_x</td><td>$css_offset_y</td></tr>
	<tr><td>CSS Offset before scaling (px)</td><td>$css_offset_before_x</td><td>$css_offset_before_y</td></tr>
	<tr><td>CSS Offset after scaling (px)</td><td>$css_offset_after_x</td><td>$css_offset_after_y</td></tr>
	</table>
	
	<p><img src=\"demo.png\" width=\"80%\" /></p>

	<div><img src=\"hardcrop.png\" /></div>
	<p>Imagemagick Hardcrop <br/> 
	$im $inputfile $im_resize -crop $im_region $outputfile</p>
	<div><img class=\"mx\" src=\"original.jpg\" /></div>
	<p>CSS Softcrop per Matrix<br/> 
	transform: matrix(" . $scaling . ", 0, 0, " . $scaling . ", " . $css_offset_before_x . ", " . $css_offset_before_y . ");</p>
	<div><img class=\"ts\" src=\"original.jpg\" /></div>
	<p>CSS Softcrop in Reihenfolge translate/scale<br/>
	transform: translate(" . $css_offset_before_x . "px, " . $css_offset_before_y . "px) scale(" . $scaling . ", " . $scaling . ");</p>
	<div><img class=\"st\" src=\"original.jpg\" /></div>
	<p>CSS Softcrop in Reihenfolge scale/translate<br/> 
	transform: scale(" . $scaling . ", " . $scaling . ") translate(" . $css_offset_after_x . "px, " . $css_offset_after_y . "px);</p>
</body>
</html>
";
fwrite( $fp, $fp_content );
fclose( $fp );

function console_log( $level, $message ){
  $log_filter=4;
  $EOL = "\n";
  if( $level <= $log_filter ){
    for( $i = 0; $i <= $level; $i++ ){ echo " "; }
    echo "$message $EOL";
  }
}

?>
