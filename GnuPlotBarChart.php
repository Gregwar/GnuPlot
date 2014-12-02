<?php

require_once './GnuPlot.php';

class GnuPlotBarChart extends \Gregwar\GnuPlot\GnuPlot{
    
    /**
     * Data groups
     */
    protected $groups = array();
    
    protected $dataLabels = array();
    
    protected $colors = array('#A61E22', '#4193D2', '#CBAC1E', '#C4BDAB', '#6E6956');

    public function __construct() {
        parent::__construct();
    }
    
    public function __destruct() {
        parent::__destruct();
    }

    /**
     * Push a new $value to exact $group, $group is a int id of group , $value is a 
     * number
     */
    public function push($group, $value, $index = null){
        $this->values[$group][] = $value;
        
        return $this->values;
    }
    
    /**
     * Push a new $group, $group is a String name of group.
     * 
     * Returns Int - group ID
     */
    public function pushGroup($group){
        $this->groups[] = $group;
        
        end($this->groups);
        return key($this->groups);
    }
    
    /**
     * Push a new $color for corresponding $group
     */
    public function setColor($group, $color){
        $this->colors[$group] = $color;
    }
    
    /**
     * Push a $label of corresponding group
     */
    public function pushLabel($label){
        $this->dataLabels[] = $label;
        
        return $this;
    }
    
    public function get(){
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
            $timeout -= 5;
        } while ($timeout>0 || $data);
        
        return $result;
    }
    
    public function plot($pieChart = false, $replot = false) {
        if ($replot) {
            $this->sendCommand('replot');
        } else {
            $this->sendCommand('plot '.$this->getUsings());
        }
        $this->plotted = true;
        
        $this->sendData();
    }

    protected function getUsings($pieChart = false) {
        $n = count($this->groups);
        if(!$n)
            return false;
        
        $usings = array();
        
        for($i = 0; $i < $n; $i++){
            $color = isset($this->colors[$i]) ? 'linecolor rgb "'.$this->colors[$i].'"' : "";
            $usings[] = "'-' u 2:xtic(1) ti col ".$color;
        }
        
        return implode(', ', $usings);
    }
    
    protected function sendData() {
        foreach ($this->groups as $groupID => $groupName) 
        {
            $this->sendCommand('"'.$groupName.'"');
            foreach ($this->values[$groupID] as $valueID => $value) {
                $this->sendCommand('"'.$this->dataLabels[$valueID].'";'.$value);
            }
            $this->sendCommand('e');
        }
    }

    protected function sendInit(){
        if(!sizeof($this->values))
            return false;
        
        $this->sendCommand('reset');
        
        if ($this->title) {
            $this->sendCommand('set title "'.$this->title.'"');
        }
        
        $this->sendCommand('set auto x');
        $this->sendCommand("set yrange [0:]");
        $this->sendCommand('set style data histogram');
        $this->sendCommand('set style histogram cluster gap 1');
        $this->sendCommand('set style fill solid border rgb "#333333"');
        $this->sendCommand('set border lw 2 lc rgb "#333333"');
        $this->sendCommand('set boxwidth 0.9');
        $this->sendCommand('set xtic scale 0 font ",12"');
        $this->sendCommand('set datafile separator ";"');
    }
}