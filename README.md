GnuPlot
=======

A PHP Library for using GnuPlot

This is the output of the `demo/write.php`:

![gnuplot](http://gregwar.com/gnuplot.png)

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

License
=======

`Gregwar\GnuPlot` is under MIT license

