<?php declare(strict_types=1);

include('../GnuPlot.php');

use Gregwar\GnuPlot\GnuPlot;

$plot = new GnuPlot();

header('Content-type: image/png');

echo $plot
    ->setGraphTitle('Demo graph')
    ->setXLabel('Something')
    ->setYLabel('Another something')
    ->setWidth(500)
    ->setHeight(300)
    ->push(0, 1)
    ->push(1, 10)
    ->push(2, 3)
    ->push(3, 2.6)
    ->push(4, 5.3)
    ->setTitle(0, 'Demo')
    ->get()
;
