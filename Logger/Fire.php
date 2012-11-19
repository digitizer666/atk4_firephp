<?php
require_once 'FirePHP/FirePHP.class.php';

class Logger_Fire extends Logger_Base {
	
	public $fire_output=null;
	
	private $firephp = null;
	
	function init() {
		parent::init();
		
		
		$this->fire_output=$this->api->getConfig('logger/fire_output','null');
		
		if ($this->fire_output) {
			if (!is_object($this->firephp))
				$this->firephp = FirePHP::getInstance(true);
			$this->firephp->setEnabled(true);			
		}
		$this->api->debug("FirePHP Logger started...");
		$this->outputInfo($this,"FirePHP Logger started...");
	}
	
	function caughtException($caller,$e) {
		if (!$this->fire_output) {
			parent::caughtException($caller,$e);
			return;
		}
		$e->shift-=1;
		
		$f=$this->firephp;
		$f->group(get_class($e));
		$f->log($e->getMessage(),array('color'=>'red'));
		$f->groupEnd();
/*
		if(method_exists($e,'getAdditionalMessage'))echo '<p><font color=red>' . $e->getAdditionalMessage() . '</font></p>';
		if($e->more_info){
			echo '<p>Additional information: <ul>';
			foreach($e->more_info as $key=>$info){
				if(is_array($info))$info=print_r($info,true);
				echo '<li>'.$key.': '.$info.'</li>';
			}
			echo '</ul></p>';
		}
		if($e->actions){
			echo '<p>Possible Actions: <ul>';
			foreach($e->actions as $key=>$val){
				echo '<li><a href="'.$this->api->getDestinationURL(null,$val).'">'.$key.'</a></li>';
			}
			echo '</ul></p>';
		}
		if(method_exists($e,'getMyFile'))echo '<p><font color=blue>' . $e->getMyFile() . ':' . $e->getMyLine() . '</font></p>';
		
		if(method_exists($e,'getMyTrace'))echo $this->backtrace($e->shift,$e->getMyTrace());
		else echo $this->backtrace($e->shift,$e->getTrace());
*/		
		exit;
	}
	function outputFatal($caller,$msg,$shift=0) {
		if (!$this->fire_output) {
			parent::outputFatal($caller,$msg,$shift);
			return;
		}
		
		$f=$this->firephp;
		$f->group("Fatal error");
		
		$f->log($this->txtLine("$msg",$frame,'fatal'));
		
		$f->log("Stack trace:");
		$f->log($this->txtBacktrace('fatal'));
		$f->groupEnd();
		exit;
		
	}
	function outputWarning($caller,$msg,$shift=0) {
		if (!$this->fire_output) {
			parent::outputWarning($caller,$msg,$shift);
			return;
		}
		$this->firephp->warn($msg);
		
	}
	function outputInfo($caller,$msg,$shift=0,$nohtml=false) {
		if (!$this->fire_output) {
			parent::outputInfo($caller,$msg,$shift,$nohtml);
			return;
		}
		
		$this->firephp->info($msg);
	}
	function outputDebug($caller,$msg,$shift=0){
		if (!$this->fire_output) {
			parent::outputDebug($caller,$msg,$shift);
			return;
		}
		$this->firephp->log($msg);
		
		
	}
}