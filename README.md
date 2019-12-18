GnuPlot
=======

A PHP Library for using GnuPlot

**WARNING: This invoke the `gnuplot` command line as back-end, which can lead to
arbitrary code execution. Be careful if you intend to use this library with
user-provided information. Have a look at [this post](https://stackoverflow.com/questions/10937597/security-risks-of-gnuplot-web-interface) for more information.**

This is the output of the `demo/write.php`:

![gnuplot](demo/out.png)

Requirements
============

You need to have a server with `gnuplot` installed and the safe mode
disabled (to be able to run `proc_open()`)

Usage
=====

There is examples in the `demo/` directory.

You can create a graph and populate it like this:

```php
<?php

use Gregwar\GnuPlot\GnuPlot;

$plot = new GnuPlot;

// Setting the main graph title
$plot->setGraphTitle('Demo graph');

// Adding three points to the first curve
$plot
    ->setTitle(0, 'The first curve')
    ->push(0, 4)
    ->push(1, 5)
    ->push(2, 6)
    ;

// Adding three points on the other curve
// (with index 1)
$plot
    ->setTitle(1, 'The first curve')
    ->push(0, 8, 1)
    ->push(1, 9, 1)
    ->push(2, 10, 2)
    ;
```

You can then save it to a file, have a look to `write.php` for example:

```php
<?php

// Write the graph to out.png
$plot->writePng('out.png');
```

Or render it directly into a browser, you can try `out.php` for
example:

```php
<?php

header('Content-type: image/png');
echo $plot->get();
```

Or display it on the screen (useful with CLI scripts), run the
`demo.php` script for example:

```php
<?php

$plot->display();
```

Or display it, and re-feed it in real time (with CLI scripts), you can
run `realTime.php` for example:

```php
<?php

$plot->refresh();
```

API
===

* `push($x, $y, $index=0)`, add a point to the $index-nth curve
* `display()`, renders the graph on the screen (asuming you are using
  it as a CLI with an X Server
* `refresh()`, same as `display()`, but will replot the graph after
  the first call
* `get()`, gets the PNG data for your image
* `writePng($filename)`, write the data to the output file
* `setTitle($index, $title)`, sets the title of the $index-nt curve
* `setGraphTitle($title)`, sets the main title for the graph
* `setXTimeFormat($format)`, sets the X axis as a time axis and specify data format
* `setXTimeFormatString($format)`, specify the X axis time presentation format
* `setXLabel($text)`, sets the label for the X axis
* `setYLabel($text)`, sets the label for the Y axis
* `setYFormat($format)`, sets Y axis formatting
* `setXRange($min, $max)`, set the X min & max
* `setYRange($min, $max)`, set the Y min & max
* `setWidth($width)`, sets the width of the graph
* `setHeight($height)`, sets the width of the graph
* `addLabel($x, $y, $text)`, add some label at a point

License
=======

`Gregwar\GnuPlot` is under MIT license
