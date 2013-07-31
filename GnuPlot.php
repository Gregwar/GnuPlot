<?php

namespace Gregwar\GnuPlot;

require_once ('GnuPlotPipe.php');

class GnuPlot
{
    // Values as an array
    protected $values = array();

    // Dimension of the Ys
    protected $dimension = null;

    // Plot width
    protected $width = 1200;

    // Plot height
    protected $height = 800;

    // X Label
    protected $xlabel = null;

    // Y Label
    protected $ylabel = null;

    // Graph labels
    protected $labels = array();

    // Titles
    protected $titles = array();

    /**
     * Reset all the values
     */
    public function reset()
    {
        $this->values = array();
        $this->dimension = null;
        $this->xlabel = null;
        $this->ylabel = null;
        $this->labels = array();
        $this->titles = array();
    }

    /**
     * Push a new data, $x is a number, $y can be a number or an array
     * of numbers
     */
    public function push($x, $y)
    {
        if (!is_array($y)) {
            $y = array($y);
        }

        if ($this->dimension === null) {
            $this->dimension = count($y);
        } else {
            if (count($y) != $this->dimension) {
                throw new \Exception('Bad dimension for push()');
            }
        }

        $this->values["$x"] = $y;

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
    public function buildPipe()
    {
        $pipe = new GnuPlotPipe;
        $pipe->sendCommand('set grid');

        if ($this->xlabel) {
            $pipe->sendCommand('set xlabel "'.$this->xlabel.'"');
        }
        
        if ($this->ylabel) {
            $pipe->sendCommand('set ylabel "'.$this->ylabel.'"');
        }

        foreach ($this->labels as $label) {
            $pipe->sendCommand('set label "'.$label[2].'" at '.$label[0].', '.$label[1]);
        }

        return $pipe;
    }

    /**
     * Runs the plot to the given pipe
     */
    public function plot(GnuPlotPipe $pipe, $replot = false)
    {
        if ($replot) {
            $pipe->sendCommand('replot');
        } else {
            $pipe->sendCommand('plot "-"'.$this->getUsings());
        }
        $this->sendData($pipe);
    }

    /**
     * Write the current plot to a file
     */
    public function writePng($file)
    {
        $pipe = $this->buildPipe();
        $pipe->sendCommand('set terminal png size '.$this->width.','.$this->height);
        $pipe->sendCommand('set output "'.$file.'"');
        $this->plot($pipe);
    }

    /**
     * Display the plot
     */
    public function display()
    {
        $pipe = $this->buildPipe();
        $this->plot($pipe);
        $this->pipe = $pipe;
    }

    /**
     * Refresh the rendering of the given pipe
     */
    public function refresh()
    {
        if ($this->pipe) {
            $this->plot($this->pipe, true);
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
        $usings = '';

        for ($i=0; $i<$this->dimension; $i++) {
            $usings .= ' using 1:'.(2+$i).' with line';
            if (isset($this->titles[$i])) {
                $usings .= ' title "'.$this->titles[$i].'"';
            }
        }

        return $usings;
    }

    /**
     * Sends all the command to the given pipe to give it the
     * current data
     */
    public function sendData(GnuPlotPipe $pipe)
    {
        foreach ($this->values as $x => $ys) {
            $data = array($x);
            foreach ($ys as $y) {
                $data[] = $y;
            }    
            $pipe->sendCommand(implode(' ', $data));
        }
        $pipe->sendCommand('e');
    }
}
