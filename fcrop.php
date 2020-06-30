#!/usr/bin/env php
<?php

/**
* fcrop - image cropping with focalpoint consideration
*
* usage:
*              fcrop <inputfile> <outputsize> <focalpoint> <outputfile>
* example:
*              php fcrop.php input.jpg 320x480 0.4,0.5 output.png
* 
* values:
* inputfile  - filename with local path, inputfile needs readable
* outputsize - pixel width and height for the file to be outputted
* focalpoint - factor x and y defining one point of interest in the picture
*              from top and left (0.5,0.5 would be centered)
* outputfile - filename with local path, directory has to be writeable
*
* dependencies: imagemagick
*
* limitations: if the focalpoint is near the border of the picture or the scaling from
*    the original to the output image is too high, imagemagick needs longer processing
*
* version:     1.0
*
*/

// config
// const
// location of imagemagick
$im = "magick"; // local path to imagemagick e.g. /usr/bin/magick
$debug = 1; // show debug logs
$info = 0; // show info log
$EOL = PHP_EOL;

// commandline processing
if( $argc != 5) die( "usage: fcrop <inputfile> <outputsize> <focalpoint> <outputfile> $EOL"
. "  e.g. php fcrop.php input.jpg 320x480 0.4,0.5 output.png $EOL");

$inputfile = $argv[1];
if( $debug ) echo "inputfile: $inputfile $EOL";

$outputsize = explode("x", $argv[2] );
$output_width = $outputsize[0];
$output_height = $outputsize[1];
if( $debug ) echo "output_width: $output_width $EOL";
if( $debug ) echo "output_height: $output_height $EOL";

$focalpoint = explode(",", $argv[3]);
$focalpoint_x = $focalpoint[0];
$focalpoint_y = $focalpoint[1];
if( $debug ) echo "focalpoint_x: $focalpoint_x $EOL";
if( $debug ) echo "focalpoint_y: $focalpoint_y $EOL";

$outputfile = $argv[4];
if( $debug ) echo "outputfile: $outputfile $EOL";

// get image geometry of inputfile
exec( "$im identify -ping -format '%w %h' $inputfile", $array_geo);
$input_geometry = explode( " ", $array_geo[0]);
$input_width = $input_geometry[0];
$input_height = $input_geometry[1];
if( $info ) echo "input_width: $input_width $EOL";
if( $info ) echo "input_height: $input_height $EOL";

// calculations
// - minimal canvas
if( $focalpoint_x > 0.5 ){ $focalpoint_x_lower = 1 - $focalpoint_x; }
 else { $focalpoint_x_lower = $focalpoint_x; }
if( $focalpoint_y > 0.5 ){ $focalpoint_y_lower = 1 - $focalpoint_y; }
 else { $focalpoint_y_lower = $focalpoint_y; }
if( $info ) echo "focalpoint_x_lower: $focalpoint_x_lower $EOL";
if( $info ) echo "focalpoint_y_lower: $focalpoint_y_lower $EOL";

// - scaling factor and maximum  
$x_scaling = round( $output_width / 2 / ( $input_width * $focalpoint_x_lower ), 4 );
$y_scaling = round( $output_height / 2 / ( $input_height * $focalpoint_y_lower ), 4 );
if( $info ) echo "x_scaling: $x_scaling $EOL";
if( $info ) echo "y_scaling: $y_scaling $EOL";
if( $x_scaling > $y_scaling ){ $scaling = $x_scaling; }
 else { $scaling = $y_scaling; }
if( $info ) echo "scaling: $scaling $EOL";

// - resized input
$resized_width = $input_width * $scaling;
$resized_height = $input_height * $scaling;
if( $info ) echo "resized_width: $resized_width $EOL";
if( $info ) echo "resized_height: $resized_height $EOL";

// - new focalpoint
$resized_focalpoint_x = $resized_width * $focalpoint_x;
$resized_focalpoint_y = $resized_height * $focalpoint_y;
if( $info ) echo "resized_focalpoint_x: $resized_focalpoint_x $EOL";
if( $info ) echo "resized_focalpoint_y: $resized_focalpoint_y $EOL";

// - offset
$resized_offset_x = round( $resized_focalpoint_x - $resized_width / 2 , 0 );
$resized_offset_y = round( $resized_focalpoint_y - $resized_height / 2 , 0 );
if( $info ) echo "resized_offset_x: $resized_offset_x $EOL";
if( $info ) echo "resized_offset_y: $resized_offset_y $EOL";

// build imagemagick command parameters
$im_resize = "-resize " . $scaling * 100 . "%";
if( $info ) echo "im_resize: $im_resize $EOL";
$im_region = "-crop " . $output_width . "x" . $output_height;
if( $resized_offset_x >= 0 ){ $im_region .= "+"; };
$im_region .= $resized_offset_x;
if( $resized_offset_y >= 0 ){ $im_region .= "+"; };
$im_region .= $resized_offset_y;
if( $info ) echo "im_region: $im_region $EOL";

// create outputfile
$im_command = "$im $inputfile $im_resize -gravity center $im_region $outputfile";
exec( "$im $inputfile $im_resize -gravity center $im_region $outputfile", $array_proc);
if( $debug ) echo "im_command: $im_command $EOL";

?>
