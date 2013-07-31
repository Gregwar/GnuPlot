<?php

namespace Gregwar\GnuPlot;

class GnuPlotPipe
{
    protected $process = null;
    protected $stdout = null;
    protected $stdin = null;

    public function __construct()
    {
        $descriptorspec = array(
            0 => array('pipe', 'r'),
            1 => array('pipe', 'w'),
            2 => array('pipe', 'r')
        );

        $this->process = proc_open('gnuplot', $descriptorspec, $pipes);

        if (!is_resource($this->process)) {
            throw new \Exception('Unable to run GnuPlot');
        }

        $this->stdin = $pipes[0];
        $this->stdout = $pipes[1];
    }

    public function __destruct()
    {
        $this->sendCommand('quit');
        proc_close($this->process);
    }

    public function sendCommand($command)
    {
        $command .= "\n";
        // echo "$command";
        fwrite($this->stdin, $command);
    }
}
