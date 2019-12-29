<?php declare(strict_types=1);

include('../GnuPlot.php');

use Gregwar\GnuPlot\GnuPlot;

$alpha = 0;
$plot  = new GnuPlot();

while (true) {
    usleep(50000);
    $plot->reset();
    $plot->setGraphTitle('The magic sinuses');
    $plot->setTitle(0, 'The moving sinus');
    $plot->setTitle(1, 'The moving cosinus');
    for ($x = 0; $x < 10; $x += 0.01) {
        $plot->push($x, sin($alpha + $x));
        $plot->push($x, cos($alpha * 1.5 + $x), 1);
    }
    $plot->plot();
    $alpha += 0.1;
}

sleep(1000);
