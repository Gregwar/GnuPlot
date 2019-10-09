<?php declare(strict_types=1);

namespace Gregwar\GnuPlot;

use Exception;

class GnuPlot
{
    // Available units
    const UNIT_BLANK = '';
    const UNIT_INCH = 'in';
    const UNIT_CM = 'cm';

    // Available terminals
    const TERMINAL_PNG = 'png';
    const TERMINAL_PDF = 'pdf dashed';
    const TERMINAL_EPS = 'eps';

    const SMOOTH_NONE = null;
    const SMOOTH_SPLINE = 'cspline';
    const SMOOTH_BEZIER = 'bezier';

    const GRID_FRONT = 'front';
    const GRID_BACK = 'back';
    const GRID_DEFAULT = 'layerdefault';

    const LINEMODE_FILLEDCURVES = 'filledcurves';

    const PROPERTY_LINES = 'lines';

    // Values as an array
    protected $values = [];

    /**
     * Time format if X data is time.
     * @var string
     */
    protected $timeFormat = null;

    // Time presentation format for X, if $timeFormat is set
    protected $timeFormatString = null;

    // Display mode
    protected $mode = 'lines';

    /** Plot width
     * @var int
     */
    protected $width = 1200;

    /** Plot height
     * @var int
     */
    protected $height = 800;

    /** Canvas height
     * @var int
     */
    protected $canvasHeight;

    /** Canvas width
     * @var int
     */
    protected $canvasWidth;

    /** Default sleep time
     * @var int
     */
    protected $sleepTime = 5000;

    // Size unit.
    protected $unit = self::UNIT_BLANK;

    /** Was it already plotted?
     * @var bool
     */
    protected $plotted = false;

    /** Should draw grid for minor ticks */
    protected $minorGrid = false;

    // Grid placement (front, back, layerdefault)
    protected $gridPlacement;

    // X Label
    protected $xlabel;

    // Y Label
    protected $ylabel;

    // Graph labels
    protected $labels;

    // Titles
    protected $titles;

    // Line Widths
    protected $lineWidths;

    // Line Modes
    protected $lineModes;

    // Line Points
    protected $linePoints;

    // Line Types
    protected $lineTypes;

    // Line Colors
    protected $lineColors;

    // Smooth Mode
    protected $lineSmooths;

    // X range scale
    protected $xrange;

    // Y range scale
    protected $yrange;

    // Graph Origin
    protected $origin;

    // X tics
    protected $xtics;

    // Y tics
    protected $ytics;

    // X mtics
    protected $mxtics;

    // Y mtics
    protected $mytics;

    // Legend position
    protected $key;

    protected $yformat = null;

    // Graph title
    protected $title;

    // Gnuplot process
    protected $process;
    protected $stdin;
    protected $stdout;

    public function __construct()
    {
        $this->initialize();
    }

    /**
     * @throws Exception
     */
    protected function initialize(): void
    {
        $this->reset();
        $this->openPipe();
    }

    /**
     * Reset all the values.
     */
    public function reset(): void
    {
        $this->values = [];
        $this->xlabel = null;
        $this->ylabel = null;
        $this->labels = [];
        $this->titles = [];
        $this->lineWidths = [];
        $this->linePoints = [];
        $this->lineModes = [];
        $this->lineTypes = [];
        $this->lineColors = [];
        $this->lineSmooths = [];
        $this->origin = [0, 0];
        $this->xrange = null;
        $this->yrange = null;
        $this->xtics = null;
        $this->ytics = null;
        $this->mxtics = null;
        $this->mytics = null;
        $this->minorGrid = false;
        $this->gridPlacement = self::GRID_DEFAULT;
        $this->title = null;
        $this->key = null;
    }

    /**
     * Open the pipe.
     *
     * @throws Exception
     */
    protected function openPipe(): void
    {
        $descriptorspec = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'r'],
        ];

        $this->process = proc_open('gnuplot', $descriptorspec, $pipes);

        if (!is_resource($this->process)) {
            throw new Exception('Unable to run GnuPlot');
        }

        $this->stdin = $pipes[0];
        $this->stdout = $pipes[1];
    }

    public function __destruct()
    {
        $this->cleanup();
    }

    protected function cleanup(): void
    {
        $this->sendCommand('quit');
        proc_close($this->process);
    }

    /**
     * Sends a command to the gnuplot process.
     *
     * @param mixed $command
     */
    public function sendCommand($command): void
    {
        $command .= PHP_EOL;
        fwrite($this->stdin, $command);
        echo $command;
    }

    public function flush(): void
    {
        $this->cleanup();
        $this->initialize();
    }

    public function __get($property)
    {
        if (self::PROPERTY_LINES === $property) {
            return count($this->values);
        }
    }

    /**
     * Sets the X Range for values.
     *
     * @param mixed $min
     * @param mixed $max
     */
    public function setXRange($min, $max)
    {
        $this->xrange = [$min, $max];

        return $this;
    }

    /**
     * Sets the Y Range for values.
     *
     * @param mixed $min
     * @param mixed $max
     */
    public function setYRange($min, $max)
    {
        $this->yrange = [$min, $max];

        return $this;
    }

    /**
     * Sets the graph origin in the canvas.
     *
     * @param mixed $min
     * @param mixed $max
     */
    public function setOrigin($min, $max)
    {
        $this->origin = [$min, $max];

        return $this;
    }

    /**
     * Sets the X tics for values.
     *
     * @param mixed $tics
     */
    public function setXTics($tics)
    {
        $this->xtics = $tics;

        return $this;
    }

    /**
     * Sets the Y tics for values.
     *
     * @param mixed $tics
     */
    public function setYTics(float $tics): self
    {
        $this->ytics = $tics;

        return $this;
    }

    /**
     * Sets the X mtics for values.
     *
     * @param mixed $tics
     */
    public function setMXTics(float $tics): self
    {
        $this->mxtics = $tics;

        return $this;
    }

    /**
     * Sets the Y mtics for values.
     *
     * @param mixed $tics
     */
    public function setMYTics(float $tics): self
    {
        $this->mytics = $tics;

        return $this;
    }

    /**
     * Enables or disabled the grid for minor tics.
     *
     * @param mixed $status
     */
    public function setMinorGrid($status)
    {
        $this->minorGrid = $status;

        return $this;
    }

    /**
     * Specifies the layer for the grid.
     *
     * @param mixed $layer
     */
    public function setGridPlacement($layer)
    {
        $this->gridPlacement = $layer;

        return $this;
    }

    /**
     * Push a new data, $x is a number, $y can be a number or an array
     * of numbers.
     *
     * @param mixed $x
     * @param mixed $y
     * @param mixed $index
     */
    public function push($x, $y, $index = 0)
    {
        if (!isset($this->values[$index])) {
            $this->values[$index] = [];
        }

        $this->values[$index][] = [$x, $y];

        return $this;
    }

    /**
     * Sets the title of the $index th curve in the plot.
     *
     * @param mixed $index
     * @param mixed $title
     */
    public function setTitle($index, $title)
    {
        $this->titles[$index] = $title;

        return $this;
    }

    /**
     * Sets the line width of the $index th curve in the plot.
     *
     * @param mixed $index
     * @param mixed $width
     */
    public function setLineWidth($index, $width)
    {
        $this->lineWidths[$index] = $width;

        return $this;
    }

    /**
     * Sets the line point of the $index th curve in the plot.
     *
     * @param mixed $index
     * @param mixed $point
     */
    public function setLinePoint($index, $point)
    {
        $this->linePoints[$index] = $point;

        return $this;
    }

    /**
     * Sets the line mode of the $index th curve in the plot.
     *
     * @param mixed $index
     * @param mixed $mode
     */
    public function setLineMode($index, $mode)
    {
        $this->lineModes[$index] = $mode;

        return $this;
    }

    /**
     * Sets the line type of the $index th curve in the plot.
     *
     * @param mixed $index
     * @param mixed $type
     */
    public function setLineType($index, $type)
    {
        $this->lineTypes[$index] = $type;

        return $this;
    }

    /**
     * Sets the line color of the $index th curve in the plot.
     *
     * @param mixed $index
     * @param mixed $color
     */
    public function setLineColor($index, $color)
    {
        $this->lineColors[$index] = $color;

        return $this;
    }

    /**
     * Sets the line smooth of the $index th curve in the plot.
     *
     * @param mixed $index
     * @param mixed $smooth
     */
    public function setLineSmooth($index, $smooth)
    {
        $this->lineSmooths[$index] = $smooth;

        return $this;
    }

    /**
     * Sets the graph width.
     *
     * @param mixed $width
     */
    public function setWidth($width)
    {
        $this->width = $width;

        return $this;
    }

    /**
     * Sets the graph height.
     *
     * @param mixed $height
     */
    public function setHeight($height)
    {
        $this->height = $height;

        return $this;
    }

    /**
     * Sets the canvas width.
     *
     * @param mixed $width
     */
    public function setCanvasWidth($width)
    {
        $this->canvasWidth = $width;

        return $this;
    }

    /**
     * Sets the canvas height.
     *
     * @param mixed $height
     */
    public function setCanvasHeight($height)
    {
        $this->canvasHeight = $height;

        return $this;
    }

    /**
     * Sets the sleep time.
     *
     * @param mixed $sleepTime
     */
    public function setSleepTime($sleepTime)
    {
        $this->sleepTime = $sleepTime;

        return $this;
    }

    /**
     * Sets the graph size unit. You can use one of the UNIT_ constants defined in this class.
     *
     * @param mixed $unit
     */
    public function setUnit($unit)
    {
        $this->unit = $unit;

        return $this;
    }

    /**
     * Sets the graph title.
     *
     * @param mixed $title
     */
    public function setGraphTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Write the current plot to a PNG file.
     *
     * @param mixed $file
     */
    public function writePng($file): void
    {
        $this->write(self::TERMINAL_PNG, $file);
    }

    /**
     * Write the current plot to a file.
     *
     * @param mixed $terminal
     * @param mixed $file
     */
    public function write($terminal, $file): void
    {
        $height = $this->canvasHeight ?: $this->height;
        $width = $this->canvasWidth ?: $this->width;

        $this->sendInit();
        $this->sendCommand("set terminal $terminal size {$width}{$this->unit}, {$height}{$this->unit}");
        $this->sendCommand('set output "' . $file . '"');

        if ($this->canvasWidth && $this->canvasHeight) {
            $this->sendCommand("set size {$this->width}, {$this->height}");
        }

        $this->plot();

        // Flush the output as described here: http://www.gnuplot.info/faq/faq.html#x1-840007.6
        $this->sendCommand('set output');
        usleep($this->sleepTime);
    }

    /**
     * Create the pipe.
     */
    protected function sendInit(): void
    {
        $gridCommand = 'set grid xtics ytics';
        if (true === $this->minorGrid) {
            $gridCommand .= ' mxtics mytics';
        }
        $gridCommand .= ' ' . $this->gridPlacement;

        $this->sendCommand($gridCommand);

        if ($this->title) {
            $this->sendCommand('set title "' . $this->title . '"');
        }

        if ($this->key) {
            $this->sendCommand('set key ' . $this->key);
        }

        if ($this->xlabel) {
            $this->sendCommand('set xlabel "' . $this->xlabel . '"');
        }

        if ($this->origin) {
            $this->sendCommand('set origin ' . current($this->origin) . ', ' . next($this->origin));
        }

        if ($this->timeFormat) {
            $this->sendCommand('set xdata time');
            $this->sendCommand('set timefmt "' . $this->timeFormat . '"');
            $this->sendCommand('set xtics rotate by 45 offset -6,-3');
            if ($this->timeFormatString) {
                $this->sendCommand('set format x "' . $this->timeFormatString . '"');
            }
        }

        if ($this->ylabel) {
            $this->sendCommand('set ylabel "' . $this->ylabel . '"');
        }

        if ($this->yformat) {
            $this->sendCommand('set format y "' . $this->yformat . '"');
        }

        if ($this->xrange) {
            $this->sendCommand('set xrange [' . $this->xrange[0] . ':' . $this->xrange[1] . ']');
        }

        if ($this->yrange) {
            $this->sendCommand('set yrange [' . $this->yrange[0] . ':' . $this->yrange[1] . ']');
        }

        if ($this->xtics) {
            $this->sendCommand('set xtics ' . $this->xtics);
        }

        if ($this->ytics) {
            $this->sendCommand('set ytics ' . $this->ytics);
        }

        if ($this->mxtics) {
            $this->sendCommand('set mxtics ' . $this->mxtics);
        }

        if ($this->mytics) {
            $this->sendCommand('set mytics ' . $this->mytics);
        }

        foreach ($this->labels as $label) {
            $this->sendCommand('set label "' . $label[2] . '" at ' . $label[0] . ', ' . $label[1]);
        }
    }

    /**
     * Runs the plot to the given pipe.
     *
     * @param mixed $replot
     */
    public function plot($replot = false): void
    {
        if ($replot) {
            $this->sendCommand('replot');
        } else {
            $this->sendCommand('plot ' . $this->getUsings());
        }
        $this->plotted = true;
        $this->sendData();
    }

    /**
     * Gets the "using" line.
     */
    protected function getUsings()
    {
        $usings = [];

        foreach ($this->values as $i => $val) {
            if (isset($this->lineModes[$i]) && self::LINEMODE_FILLEDCURVES === $this->lineModes[$i]) {
                $using = '"-" using 1:2:3 with filledcurves ';
            } else {
                $using = '"-" using 1:2 with ' . ($this->lineModes[$i] ?? $this->mode);
            }

            if (isset($this->titles[$i])) {
                $using .= ' title "' . $this->titles[$i] . '"';
            }

            if (isset($this->lineTypes[$i])) {
                $using .= ' lt ' . $this->lineTypes[$i];
            }

            if (isset($this->lineColors[$i])) {
                $using .= ' lc ' . $this->lineColors[$i];
            }

            if (isset($this->lineWidths[$i])) {
                $using .= ' lw ' . $this->lineWidths[$i];
            }

            if (isset($this->linePoints[$i])) {
                $using .= ' pt ' . $this->linePoints[$i];
            }

            if (isset($this->lineSmooths[$i])) {
                $using .= ' smooth ' . $this->lineSmooths[$i];
            }

            $usings[] = $using;
        }

        return implode(', ', $usings);
    }

    /**
     * Sends all the command to the given pipe to give it the
     * current data.
     */
    protected function sendData(): void
    {
        foreach ($this->values as $index => $data) {
            foreach ($data as $xy) {
                [$x, $y] = $xy;
                if (is_array($y)) {
                    $this->sendCommand($x . ' ' . current($y) . ' ' . next($y));
                } else {
                    $this->sendCommand($x . ' ' . $y);
                }
            }
            $this->sendCommand('e');
        }
    }

    /**
     * Write the current plot to a PDF file.
     *
     * @param mixed $file
     */
    public function writePDF($file): void
    {
        $this->write(self::TERMINAL_PDF, $file);
    }

    /**
     * Write the current plot to an EPS file.
     *
     * @param mixed $file
     */
    public function writeEPS($file): void
    {
        $this->write(self::TERMINAL_EPS, $file);
    }

    /**
     * Write the current plot to a file.
     *
     * @param mixed $format
     */
    public function get($format = self::TERMINAL_PNG)
    {
        $height = $this->canvasHeight ?: $this->height;
        $width = $this->canvasWidth ?: $this->width;

        $this->sendInit();
        $this->sendCommand("set terminal $format size {$width}{$this->unit}, {$height}{$this->unit}");

        if ($this->canvasWidth && $this->canvasHeight) {
            $this->sendCommand("set size {$this->width} {$this->height}");
        }

        fflush($this->stdout);
        $this->plot();

        // Reading data, timeout=100ms
        $result = '';
        $timeout = 100;
        do {
            stream_set_blocking($this->stdout, false);
            $data = fread($this->stdout, 128);
            $result .= $data;
            usleep($this->sleepTime);
            $timeout -= 5;
        } while ($timeout > 0 || $data);

        return $result;
    }

    /**
     * Refresh the rendering of the given pipe.
     */
    public function refresh(): void
    {
        if ($this->plotted) {
            $this->plot(true);
        } else {
            $this->display();
        }
    }

    /**
     * Display the plot.
     */
    public function display(): void
    {
        $this->sendInit();
        $this->plot();
    }

    public function setYFormat($yformat)
    {
        $this->yformat = $yformat;

        return $this;
    }

    /**
     * Sets the label for X axis.
     *
     * @param mixed $xlabel
     */
    public function setXLabel($xlabel)
    {
        $this->xlabel = $xlabel;

        return $this;
    }

    /**
     * Sets the X timeformat, example "%Y-%m-%d".
     *
     * @param mixed $timeFormat
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
     * Sets the label for Y axis.
     *
     * @param mixed $ylabel
     */
    public function setYLabel($ylabel)
    {
        $this->ylabel = $ylabel;

        return $this;
    }

    /**
     * Sets the legend position.
     *
     * @param mixed $x
     * @param mixed $y
     */
    public function setKey($x, $y)
    {
        $this->key = "$x $y";

        return $this;
    }

    /**
     * Add a label text.
     *
     * @param mixed $x
     * @param mixed $y
     * @param mixed $text
     */
    public function addLabel($x, $y, $text)
    {
        $this->labels[] = [$x, $y, $text];

        return $this;
    }

    /**
     * Histogram mode.
     */
    public function enableHistogram()
    {
        $this->mode = 'impulses linewidth 10';

        return $this;
    }
}
