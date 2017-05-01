<?php 
namespace Empiric\Libraries;
/** 
 * XML.php
 * @author Jeremy Heminger <j.heminger13@gmail.com>
 * @version 1.0.0.1
 * 
* */
use Empiric\Libraries\ErrorHandler;
use Empiric\Libraries\Abstraction;

class XML {

	public function getObj($string) {
		$xmlobj = null;
		try {
			$xmlobj = new SimpleXMLElement($string);

		} catch(Exception $e) {
			// set error message
			$GLOBALS['errors']->set_error_message($e->getMessage());
			return false;
		} 
		return $xmlobj;
	}
}