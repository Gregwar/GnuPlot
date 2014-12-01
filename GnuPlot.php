<?php

namespace Gregwar\GnuPlot;

class GnuPlot
{
    // Values as an array
    protected $values = array();
    
    // Values as an array
    protected $pieValues = array();

    // Time format if X data is time
    protected $timeFormat = null;

    // Display mode
    protected $mode = 'line';

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

    // X range scale
    protected $xrange;

    // Y range scale
    protected $yrange;

    // Graph title
    protected $title;

    // Gnuplot process
    protected $process;
    protected $stdin;
    protected $stdout;
    
    // Pie chart scale
    protected $pieScale = 0.25;
    
    // Pie chart position on paper
    protected $piePosition = 0.5;
    
    // Pie chart a
    protected $pieS = 0.05;
    
    // Pie Chart colors
    protected $colors = array('#585C00', '#750400', '#A2A811', '#2474B5', '#F5140C');

    // Pie Chart label row height
    protected $labelRowHeight = 40;

    // Pie Chart label font size
    protected $labelFontSize = 16;
    
    // Pie Chart font location
    protected $font = ""; //img/fonts/TahomaRegular.ttf
    
    // Pie Chart font color
    protected $fontColor = "#333333";
    
    // Pie Chart legend color
    protected $legendColor = "#ffffff";

    public function __construct()
    {
        $this->reset();
        $this->openPipe();
    }

    public function __destruct()
    {
        $this->sendCommand('quit');
        fclose($this->stdin);
        fclose($this->stdout);
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
            $this->sendCommand('set format x "'.$this->timeFormat.'"');
            $this->sendCommand('set xtics rotate by 45 offset -6,-3');
        }

        if ($this->ylabel) {
            $this->sendCommand('set ylabel "'.$this->ylabel.'"');
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
    public function plot($pieChart = false, $replot = false)
    {
        if ($replot) {
            $this->sendCommand('replot');
        } else {
            if(!$pieChart){
                $this->sendCommand('plot '.$this->getUsings());
            }
        }
        $this->plotted = true;
        
        if($pieChart){
            $this->sendPieData();
            $this->sendCommand('plot '.$this->getUsings($pieChart));
            return ;
        }
        
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
     * Write the current plot to a file
     */
    public function get()
    {
        $this->sendInit();
        $this->sendCommand('set terminal png size '.$this->width.','.$this->height);
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


//    private function readStream(){
//        
//    }

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
     * Sets the X timeformat
     */
    public function setXTimeFormat($timeFormat)
    {
        $this->timeFormat = $timeFormat;

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
    protected function getUsings($pieChart = false)
    {
        if($pieChart){
            return "2";
        }
        
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
    
    /**
     * Sets the pie chart label font size
     */
    public function setPieLabelFontSize($labelFontSize){
        $this->labelFontSize = $labelFontSize;
        
        return $this;
    }
    
    /**
     * Sets label of the pie chart data. If index is null it sets last 
     * inserted data title else it sets title for specified $index data;
     */
    public function setPieLabel($title, $index = null){
        if(!$index){
            $this->titles[] = $title;
            return $this;
        }
        
        $this->titles[$index] = $title;
        return $this;
    }
    
    /**
     * Sets pie chart scale
     */
    public function setPieScale($pieScale){
        $this->pieScale = $pieScale;
        
        return $this;
    }
    
    /**
     * Sets pie chart scale
     */
    public function setFontColor($fontColor){
        $this->fontColor = $fontColor;
        
        return $this;
    }
    
    /**
     * Sets pie chart scale
     */
    public function setLegendColor($legendColor){
        $this->legendColor = $legendColor;
        
        return $this;
    }

    /**
     * Push a new data for pie chart. $value is a float number betwean 0 and 1.
     */
    public function pushPie($value, $index = null){
        if(!$index){
            $this->pieValues[] = $value;
            return $this;
        }
        
        $this->pieValues[$index] = $value;
        return $this;
    }
    

    /**
     * Sends all the command to the given pipe to give it the
     * current data
     */
    protected function sendPieData()
    {
        $lVal = 0;
        $cVal = 0;
        foreach ($this->values as $index => $value) 
        {
            $cVal = $lVal + $value*360;
            if($index == (sizeof($this->values) - 1 ))
                $cVal = 360;
            
            $this->sendCommand('set obj ' . ($index+1) . ' circle arc [' . $lVal . ':' . $cVal . '] fc rgb "' . $this->colors[$index] . '" ');
            $this->sendCommand('set obj ' . ($index+1) . ' circle at screen ' . $this->piePosition . ',' . $this->piePosition . ' size screen ' . $this->pieScale . ' front');

            $lVal = $cVal;
        }
        $this->sendCommand('e');
    }
    
    /**
     * Prepares pie chart data to send
     */
    protected function preparePieData(){
        $sum = 0;
        $n = sizeof($this->pieValues);
        
        for($i = 0; $i < $n; $i++){
            $sum += $this->pieValues[$i];
        }
        
        foreach ($this->pieValues as $key => $value){
            $this->values[$key] = ($value/$sum);
        }
    }
    
    protected function sendPieInit(){
        if(!sizeof($this->values))
            return false;
        
        if ($this->title) {
            $this->sendCommand('set title "'.$this->title.'"');
        }
        
        $this->sendCommand('reset');
        $this->sendCommand('unset border');
        $this->sendCommand('unset tics');
        $this->sendCommand('unset key');
        $this->sendCommand('set angles degree');
        $this->sendCommand('set yrange [0:1]');
        $this->sendCommand('set style fill solid 1.0 border -2');
    }

    /**
     * Write the current plot to a file
     */
    public function getPieChart()
    {
        $this->preparePieData();
        $this->sendPieInit();
        $this->sendCommand('set terminal png size '.$this->width.','.$this->height);
        fflush($this->stdout);
        $this->plot(true);
        
        // Reading data, timeout=100ms
        $result = '';
        $timeout = 100;
        do {
            stream_set_blocking($this->stdout, false);
            $data = fread($this->stdout, 128);
            $result .= $data;
            usleep(5000);
            $timeout -= 5;
        } while ($timeout>0 || $data);

        $imgChart = imagecreatefromstring($result);
        $imgLabel = $this->getLabelImg();
        
        $hheight = imagesy($imgLabel);
        
        imagecopy($imgLabel, $imgChart, 0, 0, 0, 0, $this->width, $hheight);
        
        $this->drawLabels($imgLabel);
        
        return imagepng($imgLabel);
    }
    
    protected function getLabelImg(){
        $height = $this->height + sizeof($this->titles) * $this->labelRowHeight;
        
        $image = imagecreatetruecolor($this->width, $height);
        $white = imagecolorallocatealpha($image, 255, 255, 255, 127);
        imagefill($image, 0, 0, $white);
        return $image;
    }
    
    protected function drawLabels($image){
        $rgb = $this->hexToRGB($this->fontColor);
        $fontColor = imagecolorallocate($image, $rgb['r'], $rgb['g'], $rgb['b']);
        
        $rgb = $this->hexToRGB($this->legendColor);
        $legendColor = imagecolorallocate($image, $rgb['r'], $rgb['g'], $rgb['b']);
        
        $x1 = 20;
        $x2 = 40;
        $labelX = 10;
        
        imagefilledrectangle($image, $labelX, $this->height-$labelX, $this->width - $labelX, imagesy($image)-3, $legendColor);
        
        foreach ($this->titles as $key => $value) {
            $rgb = $this->hexToRGB($this->colors[$key]);
            $color = imagecolorallocate($image, $rgb['r'], $rgb['g'], $rgb['b']);
            
            $y = $this->height + $key * $this->labelRowHeight;
            $y2 = $y + 30;
            
            $value .= ' ' . round($this->values[$key]*100, 2) . '%';
            
            if($this->font){
                imagettftext($image, $this->labelFontSize, 0, $x2+8, $y2-7, $fontColor, $this->font, $value);
            } else {
                imagestring($image, $this->labelFontSize, $x2+8, $y+8, $value, $fontColor);
            }
            
            imagefilledrectangle($image, $x1, $y, $x2, $y2, $color);
        }
    }
    
    protected function hexToRGB ($hexColor){
        if( preg_match( '/^#?([a-h0-9]{2})([a-h0-9]{2})([a-h0-9]{2})$/i', $hexColor, $matches ) )
        {
            return array(
                'r' => hexdec( $matches[ 1 ] ),
                'g' => hexdec( $matches[ 2 ] ),
                'b' => hexdec( $matches[ 3 ] )
            );
        }
        else
        {
            return array(
                'r' => 0,
                'g' => 0,
                'b' => 0
            );
        }
    }
}