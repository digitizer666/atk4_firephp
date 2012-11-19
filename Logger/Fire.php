<?php
require_once 'FirePHP/FirePHP.class.php';

class Logger_Fire extends Logger_Base {
	
	public $fire_output=null;
	
	private $firephp = null;
	
	function init() {
		parent::init();
		
#		$this->api->debug=true;
		$this->fire_output=$this->api->getConfig('logger/fire_output',null);
		
		if (!is_null($this->fire_output)) {
			if (!is_object($this->firephp))
				$this->firephp = FirePHP::getInstance(true);
			$this->firephp->setEnabled(true);
			$this->outputInfo($this,"FirePHP Logger started...");
			
		}
	}
	
	function backtrace($sh=null,$backtrace=null){
		if (is_null($this->fire_output)) {
			parent::backtrace($sh,$backtrace);
			return;
		}
		if(!isset($backtrace)) $backtrace=debug_backtrace();
	
		$n=0;
		foreach($backtrace as $bt){
			$n++;
			$args = '';
			if(!isset($bt['args']))continue;
			foreach($bt['args'] as $a){
				if(!empty($args)){
					$args .= ', ';
				}
				switch (gettype($a)) {
					case 'integer':
					case 'double':
						$args .= $a;
						break;
					case 'string':
						$a = htmlspecialchars(substr($a, 0, 128)).((strlen($a) > 128) ? '...' : '');
						$args .= "\"$a\"";
						break;
					case 'array':
						$args .= "Array(".count($a).")";
						break;
					case 'object':
						$args .= "Object(".get_class($a).")";
						break;
					case 'resource':
						$args .= "Resource(".strstr($a, '#').")";
						break;
					case 'boolean':
						$args .= $a ? 'True' : 'False';
						break;
					case 'NULL':
						$args .= 'Null';
						break;
					default:
						$args .= 'Unknown';
				}
			}
	
			if(($sh==null && strpos($bt['file'],'/atk4/lib/')===false) || (!is_int($sh) && $bt['function']==$sh)){
				$sh=$n;
			}
	
			$name=(!isset($bt['object']->name))?get_class($bt['object']):$bt['object']->name;
			if($bt['object'])$x = $name;else $x="";
			$output[]=array(dirname($bt['file']),basename($bt['file']),$bt['line'],$x,get_class($bt['object']),$bt['type'],$bt['function']."(".$args.")");
		}
		return array("Backtrace",$output);
	}
	
	function caughtException($caller,$e) {
		if (is_null($this->fire_output)) {
			parent::caughtException($caller,$e);
			return;
		}
		$e->shift-=1;
		
		$f=$this->firephp;
		$f->group(get_class($e));
		$f->log($e->getMessage(),array('color'=>'red'));

		if(method_exists($e,'getAdditionalMessage')) $f->log($e->getAdditionalMessage());
		if($e->more_info){
			$table   = array();
			foreach($e->more_info as $key=>$info){
				if(is_array($info))$info=print_r($info,true);
				$table[] = array($key,$info);
			}
			$f->table("Additional information:",$table);
		}
		if($e->actions){
			$table   = array();
			foreach($e->actions as $key=>$val){
				$table[] = array($key,$val);
			}
			$f->table("Possible Actions:",$table);
		}
		if(method_exists($e,'getMyFile')) $f->log($e->getMyFile().':'.$e->getMyLine());
		
		if(method_exists($e,'getMyTrace'))$t=$this->backtrace($e->shift,$e->getMyTrace());
		else $t=$this->backtrace($e->shift,$e->getTrace());
		$f->table($t[0],$t[1], array('Collapsed' => false));
		
		$f->groupEnd();
		
		exit;
	}
	function outputFatal($caller,$msg,$shift=0) {
		if (is_null($this->fire_output)) {
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
		if (is_null($this->fire_output)) {
			parent::outputWarning($caller,$msg,$shift);
			return;
		}
		$this->firephp->warn($msg);
		
	}
	function outputInfo($caller,$msg,$shift=0,$nohtml=false) {
		if (is_null($this->fire_output)) {
			parent::outputInfo($caller,$msg,$shift,$nohtml);
			return;
		}
		
		$this->firephp->info($msg);
	}
	function outputDebug($caller,$msg,$shift=0){
		if (is_null($this->fire_output)) {
			parent::outputDebug($caller,$msg,$shift);
			return;
		}
		$this->firephp->log($msg);
		
		
	}
}