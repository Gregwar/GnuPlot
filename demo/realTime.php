<?php

include ('../GnuPlot.php');

use Gregwar\GnuPlot\GnuPlot;

$alpha = 0;
$plot = new GnuPlot;

while (true) {
    usleep(50000);
    $plot->reset();
    for ($x=0; $x<10; $x+=0.01) {
        $plot->push($x, sin($alpha+$x));
    }
    $alpha += 0.1;
    $plot->refresh();
}

sleep(1000);
