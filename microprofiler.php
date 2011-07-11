<?php
class microprofiler{
	public $profiles = array();
	private $dump_on_jump = false;
	private $doj_args = array();
	private $doj_delimiter = "\n";
	private $profile;
	private $enabled = false;
	private $start_time;
	
	public function __construct($name = array(), $enable = true){
		$this->start_time = explode(' ', microtime());
		$this->start_memory = memory_get_usage();
		$this->jump($name);
		if($enable) $this->enable();
	}
	
	public function tick(){
		$backtrace = debug_backtrace();
		$debug = end($backtrace);
		$this->profile[0]++;
		if($this->profile[1][0] === 0) $this->profile[1][0] = microtime();
		$this->profile[1][1] = microtime();
		if($this->profile[2][0] === 0) $this->profile[2][0] = memory_get_usage();
		$this->profile[2][1] = memory_get_usage();
		if(!in_array($debug['line'], $this->profile[3])) $this->profile[3][] = $debug['line'];
	}
	
	public function jump($name = array()){
		if($this->dump_on_jump){
			call_user_func_array(array($this, 'dump'), $this->doj_args);
			echo $this->doj_delimiter;
		}
		$new = false;
		if(!is_scalar($name)) $new = true;
		if($new){
			$this->profile = &$this->profiles[];
			$this->profile = array(0, array(0, 0), array(0,0), array());
		}else{
			if(!isset($this->profiles[$name])) $this->profiles[$name] = array(0, array(0, 0), array(0, 0), array());
			$this->profile = &$this->profiles[$name];
		}
		return true;
	}
	
	public function dump_on_jump($enabled = false, $args = array(), $delimiter = "\n"){
		if($enabled && is_array($args)){
			$this->doj_args = $args;
			$this->doj_delimiters = $delimiter;
			$this->dump_on_jump = true;
		}else{
			$this->dump_on_jump = false;
		}
	}
	
	public function enable(){
		if($this->enabled) return false;
		register_tick_function(array($this, 'tick'));
		$this->enabled = true;
		return true;
	}
	
	public function disable(){
		if(!$this->enabled) return false;
		unregister_tick_function(array($this, 'tick'));
		$this->enabled = false;
		return true;
	}
	
	public function human_readable($size, $round = 4){
		if($size == 0) return '0B';
		$units = array('B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB');
		$sized = round($size / pow(1024, ($i = floor(log(abs($size), 1024)))), $round);
		$top = count($units) - 1;
		if($i > $top){
			$sized = pow($sized, $i - $top);  //This line is incorrect.  It needs re-consideration on logic.  This was just something for me to throw in real quick.  PS: It's 2011.  If you've got a file that is over 1024 YiB, you should be more famous than Google.  This means that this line should never be parsed until AT LEAST the year [insert year that YiB has ben reached here].
			$i = $top;
		}
		return $sized . $units[$i];
	}
	
	private function gather_nums($nums, $crush = true){
		$out = array();
		reset($nums);
		$ele = &$out[];
		$ele[0] = current($nums);
		while(true){
			$cur = current($nums);
			$next = next($nums);
			if($next === $cur + 1) continue;
			$ele[1] = $cur;
			if($next === false) break;
			$ele = &$out[];
			$ele[0] = $next;
		}
		$ele[1] = end($nums);
		unset($ele);
		if($crush){
			foreach($out as &$s) if($s[0] === $s[1]) $s = $s[0];
			unset($s);
		}
		return $out;
	}
	
	public function dump($return = false, $before = '', $delimiter = "\n", $after = "\n", $spaceout = 2){
		$output = $ret = array();
		$lastlen = $longest = 0;
		foreach($this->profiles as $name => $profile){
			$start_time = explode(' ', $profile[1][0]);
			$end_time = explode(' ', $profile[1][1]);
			$expl = (($usage = $this->human_readable($profile[2][1] - $profile[2][0])) >= 0) ? 'used' : 'freed';
			$elapsed = bcadd(bcsub($end_time[1], $start_time[1], 6), bcsub($end_time[0], $start_time[0] ,6), 6);
			$lout = array();
			foreach($this->gather_nums($profile[3]) as $nums){
				if(is_array($nums)) $lout[] = "{$nums[0]}~{$nums[1]}";
				elseif(is_int($nums)) $lout[] = (string)$nums;
			}
			$loutput = '{' . implode(', ', $lout) . '}';
			if($spaceout > 0){
				$lastlen = strlen($loutput);
				if($lastlen > $longest) $longest = $lastlen;
				$output[] = array("[$name] $loutput", "$elapsed seconds, {$profile[0]} ticks, " . trim($usage, '-') . " memory $expl.", $lastlen);
			}else $output[] = "[$name] $loutput $elapsed seconds, {$profile[0]} ticks, " . trim($usage, '-') . " memory $expl.";
		}
		if($spaceout > 0) foreach($output as $out) $ret[] = $out[0] . str_repeat(' ', $longest - ($out[2] - $spaceout)) . $out[1];
		else $ret = &$output;
		$endmark = explode(' ', microtime());
		$elapsed = bcadd(bcsub($endmark[1], $this->start_time[1], 6), bcsub($endmark[0], $this->start_time[0], 6), 6);
		$ret[] = "$elapsed seconds have elapsed since the profiler was instantiated.";
		$expl = (($usage = $this->human_readable(memory_get_usage() - $this->start_memory)) >= 0) ? 'used' : 'freed';
		$ret[] = "$usage memory has been $expl since this profiler was instantiated.";
		if($return) return $before . implode($delimiter, $ret) . $after;
		return print $before . implode($delimiter, $ret) . $after;
	}
	
	public function __destruct(){
		unregister_tick_function(array($this, 'tick'));
	}
}