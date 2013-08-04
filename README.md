GnuPlot
=======

A PHP Library for using GnuPlot

For usage demo, see `demo/` directory

Usage
=====

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

You can then save it to a file:

```php
// Write the graph to out.png
$plot->writePng('out.png');
```

License
=======

`Gregwar\GnuPlot` is under MIT license

