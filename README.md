# fcrop
image cropping with focalpoint consideration

php commandline script uses imagemagick for cropping with focalpoint consideration


## focalpoint

![examples with focalpoint](https://github.com/jrgdrs/fcrop/blob/master/examples/nightwatch_measure.png?raw=true)



By selecting different focalpoints the resulting crop is adapted and the picture is rendered.


php fcrop.php nightwatch.jpg 300x600 0.48,0.6 nightwatch.out.png

php fcrop.php nightwatch.jpg 200x800 0.2,0.6 nightwatch.out.png

php fcrop.php nightwatch.jpg 200x200 0.03,0.28 nightwatch.out.png

php fcrop.php nightwatch.jpg 400x400 0.32,0.25 nightwatch.out.png

[php fcrop.php nightwatch.jpg 400x200 0.75,0.4 nightwatch.out.png] (http://htmlpreview.github.io/?https://raw.githubusercontent.com/jrgdrs/fcrop/master/examples/075040/index.html)

## examples

Find in the examples folder the resulting images including a description as index.html with the hardcrop for imagemagick and also different versions of CSS softcrop parameters to get the same clipping results.


