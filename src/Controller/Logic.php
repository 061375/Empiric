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
use Empiric\Libraries\CMD;
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
		            'name'=>'cliRunShallow',
		            'flag'=>'s',
		            'vars'=>array(
		                '--dir='
		            )
		        ),array(
		            'name'=>'cliRunDeep',
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

function cliRunShallow($params) {

}

function cliRunDeep($params) {
	
}