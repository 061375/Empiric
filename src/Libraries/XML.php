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

	function __construct() {
		libxml_use_internal_errors(true);
	}
	/** 
	 * @param $fileStr
	 * @return object
	 * */
	public function getObj($fileStr) {
		$xmlAll = $this->get_parts($fileStr);
		$xml = null;
		try {
			$xml = new DOMDocument(); 
			$xml->loadXML($xmlAll['xml']);
			if(false === $xml->schemaValidateSource($xmlAll['schema'])) {
				$this->get_errors();
				return false;	
			}
		} catch(Exception $e) {
			// set error message
			$GLOBALS['errors']->set_error_message($e->getMessage());
			return false;
		} 
		return $xml;
	}
	/** 
	 * gets the XML and SCHEMA
	 * @param string $file
	 * @return string
	 * */
	public function get_parts($file) {
		$schema = true;

		$return = array();

		$lines = explode("\n",$file);

		foreach ($lines as $line) {
			if(true === $schema) {
				$return['schema'] .= $line;
				if(strpos($line,'</xs:schema>') !== false) {
					$schema = false;
				}
			}else{
				$return['xml'] .= $line;
			}
		}
		return $return;
	}

	// private 

	/** 
	 * @return void
	 * */
	private function get_errors() {
		$errors = libxml_get_errors();
	    foreach ($errors as $error) {
	        $GLOBALS['errors']->set_error_message(libxml_display_error($error));
	    }
	    libxml_clear_errors();
	}
	/** 
	 * @param object $error
	 * @return string
	 * */
	private function mk_readable_err($error) {
		$return = "\n";
	    switch ($error->level) {
	        case LIBXML_ERR_WARNING:
	            $return .= "Warning $error->code: ";
	            break;
	        case LIBXML_ERR_ERROR:
	            $return .= "Error $error->code: ";
	            break;
	        case LIBXML_ERR_FATAL:
	            $return .= "Fatal Error $error->code: ";
	            break;
	    }
	    $return .= trim($error->message);
	    if ($error->file) {
	        $return .=    " in $error->file";
	    }

	    $return .= " on line $error->line\n";

	    return $return;
	}
}