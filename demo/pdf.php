<?php declare(strict_types=1);

include('../GnuPlot.php');

use Gregwar\GnuPlot\GnuPlot;

$plot = new GnuPlot();

$plot
    // Setting graph main title
    ->setGraphTitle('Demo graph')
    // Setting X & Y labels
    ->setXLabel('Something')
    ->setYLabel('Another something')
    // Set the unit to inches
    ->setUnit(GnuPlot::UNIT_INCH)
    // Setting graph dimensions to those of an A4 sheet
    ->setWidth(11.02)
    ->setHeight(8.27)
    // Demo curve (index=0)
    ->setTitle(0, 'Demo')
    ->push(0, 1)
    ->push(1, 10)
    ->push(2, 3)
    ->push(3, 2.6)
    ->push(4, 5.3)
    // Other curve, (index=1)
    ->setTitle(1, 'Other curve')
    ->push(0, 3.9, 1)
    ->push(1, 2.3, 1)
    ->push(2, 4.3, 1)
    ->push(3, 3.1, 1)
    ->push(4, 5.2, 1)
    // Pointing out a value
    ->addLabel(2, 4.3, 'An important point')
    // Writing to out.png
    ->writePDF('out.pdf')
;
