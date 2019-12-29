<?php declare(strict_types=1);

include('../GnuPlot.php');

use Gregwar\GnuPlot\GnuPlot;

$plot = new GnuPlot();

$plot
    ->setXLabel('X')
    ->setYRange(0, 20)
    ->setYLabel('Y')
    ->push(0, 1)
    ->push(1, 10)
    ->push(2, 3)
    ->addLabel(2, 3, 'This is a good point')
    ->push(3, 2.6)
    ->push(4, 5.3)
    ->setTitle(0, 'The curve')
    ->display()
;

sleep(1000);
