<?php

include ('../GnuPlot.php');

use Gregwar\GnuPlot\GnuPlot;

$plot = new GnuPlot;

$plot
    ->setXLabel('X')
    ->setYRange(-1, 5)
    ->setXTimeFormat('%d/%m/%Y')
    ->setYLabel('Y')
    ->push('10/07/1989', 0)
    ->push('11/07/1989', 1)
    ->push('12/07/1989', 3)
    ->setTitle(0, 'The curve')
    ->writePng('date.png')
    ;
