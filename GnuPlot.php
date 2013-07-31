<?php

namespace Gregwar\GnuPlot;

class GnuPlot
{
    // Values as an array
    protected $values = array();

    // Plot width
    protected $width = 1200;

    // Plot height
    protected $height = 800;

    // Was it already plotted?
    protected $plotted = false;

    // X Label
    protected $xlabel;

    // Y Label
    protected $ylabel;

    // Graph labels
    protected $labels;

    // Titles
    protected $titles;

    // Gnuplot process
    protected $process;
    protected $stdin;
    protected $stdout;

    public function __construct()
    {
        $this->reset();
        $this->openPipe();
    }
    
    public function __destruct()
    {
        $this->sendCommand('quit');
        proc_close($this->process);
    }

    /**
     * Reset all the values
     */
    public function reset()
    {
        $this->values = array();
        $this->xlabel = null;
        $this->ylabel = null;
        $this->labels = array();
        $this->titles = array();
    }

    /**
     * Push a new data, $x is a number, $y can be a number or an array
     * of numbers
     */
    public function push($x, $y, $index = 0)
    {
        if (!isset($this->values[$index])) {
            $this->values[$index] = array();
        }

        $this->values[$index][] = array($x, $y);

        return $this;
    }

    /**
     * Sets the title of the $index th curve in the plot
     */
    public function setTitle($index, $title)
    {
        $this->titles[$index] = $title;

        return $this;
    }

    /**
     * Sets the graph width
     */
    public function setWidth($width)
    {
        $this->width = $width;

        return $this;
    }

    /**
     * Sets the graph height
     */
    public function setHeight($height)
    {
        $this->height = $height;

        return $this;
    }

    /**
     * Create the pipe
     */
    public function sendInit()
    {
        $this->sendCommand('set grid');

        if ($this->xlabel) {
            $this->sendCommand('set xlabel "'.$this->xlabel.'"');
        }
        
        if ($this->ylabel) {
            $this->sendCommand('set ylabel "'.$this->ylabel.'"');
        }

        foreach ($this->labels as $label) {
            $this->sendCommand('set label "'.$label[2].'" at '.$label[0].', '.$label[1]);
        }
    }

    /**
     * Runs the plot to the given pipe
     */
    public function plot($replot = false)
    {
        if ($replot) {
            $this->sendCommand('replot');
        } else {
            $this->sendCommand('plot '.$this->getUsings());
        }
        $this->plotted = true;
        $this->sendData();
    }

    /**
     * Write the current plot to a file
     */
    public function writePng($file)
    {
        $this->sendInit();
        $this->sendCommand('set terminal png size '.$this->width.','.$this->height);
        $this->sendCommand('set output "'.$file.'"');
        $this->plot();
    }

    /**
     * Display the plot
     */
    public function display()
    {
        $this->sendInit();
        $this->plot();
    }

    /**
     * Refresh the rendering of the given pipe
     */
    public function refresh()
    {
        if ($this->plotted) {
            $this->plot(true);
        } else {
            $this->display();
        }
    }

    /**
     * Sets the label for X axis
     */
    public function setXLabel($xlabel)
    {
        $this->xlabel = $xlabel;

        return $this;
    }

    /**
     * Sets the label for Y axis
     */
    public function setYLabel($ylabel)
    {
        $this->ylabel = $ylabel;

        return $this;
    }

    /**
     * Add a label text
     */
    public function addLabel($x, $y, $text)
    {
        $this->labels[] = array($x, $y, $text);

        return $this;
    }

    /**
     * Gets the "using" line
     */
    public function getUsings()
    {
        $usings = array();

        for ($i=0; $i<count($this->values); $i++) {
            $using = '"-" using 1:2 with line';
            if (isset($this->titles[$i])) {
                $using .= ' title "'.$this->titles[$i].'"';
            }
            $usings[] = $using;
        }

        return implode(', ', $usings);
    }

    /**
     * Sends all the command to the given pipe to give it the
     * current data
     */
    public function sendData()
    {
        foreach ($this->values as $index => $data) {
            foreach ($data as $xy) {
                list($x, $y) = $xy;
                $this->sendCommand($x.' '.$y);
            }
            $this->sendCommand('e');
        }
    }

    /**
     * Sends a command to the gnuplot process
     */
    public function sendCommand($command)
    {
        $command .= "\n";
        fwrite($this->stdin, $command);
    }

    /**
     * Open the pipe
     */
    protected function openPipe()
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
}
