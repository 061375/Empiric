<?php 
namespace Empiric\Controller;
/** 
 * 
 * Logic.php
 * @author Jeremy Heminger <j.heminger13@gmail.com>
 * @version 1.0.0.1
 * Gathers user input and makes descisions about what to do
 *
 *
 * */
use Empiric\Libraries\General;
use Empiric\Libraries\ErrorHandler;
use Empiric\Libraries\CMD;
use Empiric\Libraries\MalwareChk;
use Symfony\Component\Yaml\Yaml;
class Logic {
	/** 
	 * @param boolean, array
	 * */
	function __construct($ajax = false) {
		if (CMD::iscmd(false) === 'cli') {
			$this->cli();
		}elseif(false !== $ajax) {
			$this->ajax();
		}else{
			$this->method($ajax);
		}
	}
	/** 
	 * 
	 * */
	private function cli() {
		CMD::runall(
		    array(
		    	array(
		            'name'=>'\Empiric\Controller\cliSetVerbose',
		            'flag'=>'v'
		        ),
		        array(
		            'name'=>'\Empiric\Controller\cliSetTesting',
		            'flag'=>'t'
		        ),
		        array(
		            'name'=>'\Empiric\Controller\cliRunShallow',
		            'flag'=>'s',
		            'vars'=>array(
		                '--dir='
		            )
		        ),array(
		            'name'=>'\Empiric\Controller\cliRunDeep',
		            'flag'=>'d',
		            'vars'=>array(
		                '--dir='
		            )
		        )
		    ),'web access not allowed');

	}
	/** 
	 * @todo 
	 * */
	private function ajax() {
		
	}
	/** 
	 * @todo
	 * */
	private function method($method) {
		
	}
}
/** 
 * initEmperic 
 * gets any configuration files and instantiates the MalwareChk class
 * @param array $params
 * @return object MalwareChk
 * */
function initEmperic($params) {
	$GLOBALS['errors'] = new ErrorHandler();

	// get all yaml files from config folder in case its neccessasry to add more in the future
	$configs = General::recurse_get_files(getcwd().'/config','.yml');
	foreach ($configs as $f) {
		$name = str_replace('.yml','',basename($f));
		$c[$name] = Yaml::parse(file_get_contents($f));
	}
	if(!isset($c['config'])) {
		// set error message
		$GLOBALS['errors']->set_error_message('at least one config file must be set and at least one config file must be named config.yml');
		// display errors
		$GLOBALS['errors']->display_errors(true,true);
	}

	$m = new MalwareChk($c);
	return $m;
}
/** 
 * cliRunShallow
 * this is a basic pattern match based on common attack vectors that is run via command-line
 * @param array $params
 * @return void
 * */
function cliRunShallow($params) {
	$locations = isset($params['dir']) ? $params['dir'] : './';
	$locations = str_replace('{','',$locations);
	$locations = str_replace('}','',$locations);
	if(strpos(',',$locations) !== false) {
		$locations = explode(',',$locations);
	} else {
		$locations = array($locations);
	}
	$m = initEmperic($params);
	$result = $m->shallowSearch($locations);
	if(true === $GLOBALS['errors']->has_error()) {
		$GLOBALS['errors']->display_errors(true,true);
	}
	if(false === $result) {
		die("\nan unknown error occured\n");
	}
	die("\noperation complete\n");
}
/** 
 * cliRunDeep
 * this search compares code patters based on known attack vectors 
 * but compares witin a percentage match of an original attack vector
 * @param array $params
 * @return void
 * */
function cliRunDeep($params) {
	
}

function cliSetVerbose() {
	$GLOBALS['verbose'] = true;	
}
function cliSetTesting() {
	$GLOBALS['testing'] = true;	
}