<?php namespace Gregwar\GnuPlot;

class GnuPlot {
	// Available units
	const UNIT_BLANK	= '';
	const UNIT_INCH 	= 'in';
	const UNIT_CM		= 'cm';
	
	// Available terminals
	const TERMINAL_PNG	= 'png';
	const TERMINAL_PDF	= 'pdf';
	const TERMINAL_EPS	= 'eps';
	
    // Values as an array
    protected $values = array();

    // Time format if X data is time
    protected $timeFormat = null;

    // Time presentation format for X, if $timeFormat is set
    protected $timeFormatString = null;

    // Display mode
    protected $mode = 'line';

    // Plot width
    protected $width = 1200;

    // Plot height
    protected $height = 800;
	
	// Size unit.
	protected $unit = self::UNIT_BLANK;

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

    // X range scale
    protected $xrange;

    // Y range scale
    protected $yrange;

    protected $yformat = null;

    // Graph title
    protected $title;

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
        $this->xrange = null;
        $this->yrange = null;
        $this->title = null;
    }

    /**
     * Sets the X Range for values
     */
    public function setXRange($min, $max)
    {
        $this->xrange = array($min, $max);

        return $this;
    }

    /**
     * Sets the Y Range for values
     */
    public function setYRange($min, $max)
    {
        $this->yrange = array($min, $max);

        return $this;
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
     * Sets the graph size unit. You can use one of the UNIT_ constants defined in this class.
     */
	public function setUnit($unit)
	{
		$this->unit = $unit;

		return $this;
	}

    /**
     * Sets the graph title
     */
    public function setGraphTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Create the pipe
     */
    protected function sendInit()
    {
        $this->sendCommand('set grid');

        if ($this->title) {
            $this->sendCommand('set title "'.$this->title.'"');
        }

        if ($this->xlabel) {
            $this->sendCommand('set xlabel "'.$this->xlabel.'"');
        }

        if ($this->timeFormat) {
            $this->sendCommand('set xdata time');
            $this->sendCommand('set timefmt "'.$this->timeFormat.'"');
            $this->sendCommand('set xtics rotate by 45 offset -6,-3');
            if ($this->timeFormatString) {
                $this->sendCommand('set format x "'.$this->timeFormatString.'"');
            }
        }

        if ($this->ylabel) {
            $this->sendCommand('set ylabel "'.$this->ylabel.'"');
        }

        if ($this->yformat) {
            $this->sendCommand('set format y "'.$this->yformat.'"');
        }

        if ($this->xrange) {
            $this->sendCommand('set xrange ['.$this->xrange[0].':'.$this->xrange[1].']');
        }

        if ($this->yrange) {
            $this->sendCommand('set yrange ['.$this->yrange[0].':'.$this->yrange[1].']');
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
	public function write($terminal, $file)
	{
		$this->sendInit();
		$this->sendCommand("set terminal $terminal size {$this->width}{$this->unit}, {$this->height}{$this->unit}");
		$this->sendCommand('set output "'.$file.'"');
		$this->plot();
	}

    /**
     * Write the current plot to a PNG file
     */
    public function writePng($file)
    {
        $this->write(self::TERMINAL_PNG, $file);
    }
	
	/**
     * Write the current plot to a PDF file
     */
	public function writePDF($file)
	{
		$this->write(self::TERMINAL_PDF, $file);
	}
	
	/**
     * Write the current plot to an EPS file
     */
	public function writeEPS($file)
	{
		$this->write(self::TERMINAL_EPS, $file);
	}

    /**
     * Write the current plot to a file
     */
    public function get($format = self::TERMINAL_PNG)
    {
        $this->sendInit();
		$this->sendCommand("set terminal $format size {$this->width}{$this->unit}, {$this->width}{$this->unit}");
        fflush($this->stdout);
        $this->plot();

        // Reading data, timeout=100ms
        $result = '';
        $timeout = 100;
        do {
            stream_set_blocking($this->stdout, false);
            $data = fread($this->stdout, 128);
            $result .= $data;
            usleep(5000);
            $timeout-=5;
        } while ($timeout>0 || $data);

        return $result;
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

    public function setYFormat($yformat)
    {
        $this->yformat = $yformat;

        return $this;
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
     * Sets the X timeformat, example "%Y-%m-%d"
     */
    public function setXTimeFormat($timeFormat)
    {
        $this->timeFormat = $timeFormat;

        return $this;
    }

    public function setTimeFormatString($timeFormatString)
    {
        $this->timeFormatString = $timeFormatString;

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
     * Histogram mode
     */
    public function enableHistogram()
    {
        $this->mode = 'impulses linewidth 10';

        return $this;
    }

    /**
     * Gets the "using" line
     */
    protected function getUsings()
    {
        $usings = array();

        for ($i=0; $i<count($this->values); $i++) {
            $using = '"-" using 1:2 with '.$this->mode;
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
    protected function sendData()
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
    protected function sendCommand($command)
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
