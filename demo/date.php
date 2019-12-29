<?php declare(strict_types=1);

include('../GnuPlot.php');

use Gregwar\GnuPlot\GnuPlot;

$plot = new GnuPlot();

$plot
    ->setXLabel('Date')
    ->setYRange(-1, 7)
    ->setXTimeFormat('%d/%m/%Y')
    ->enableHistogram()
    ->setYLabel('Y')
    ->push('10/07/1989', 0)
    ->push('11/07/1989', 1)
    ->push('12/07/1989', 3)
    ->push('13/07/1989', 3)
    ->push('14/07/1989', 2)
    ->push('15/07/1989', 6)
    ->push('16/07/1989', 3)
    ->push('17/07/1989', 2)
    ->setTitle(0, 'The curve')
    ->setWidth(400)
    ->setHeight(300)
    ->writePng('date.png')
;
