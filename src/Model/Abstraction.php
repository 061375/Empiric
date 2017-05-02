<?php 
namespace Empiric\Model;
/** 
* 
* Abstraction.php
* @author Jeremy Heminger <j.heminger13@gmail.com>
* @version 1.0.0.1
* 
* Not abstraction layer in the traditional sense. 
* This will control the read-write of the definitions file.
* If the file is locked, the script will loop until it either gets an unlock or times out
* 
* The data stored as XML is searchable with a simple SQL markup that will be equivilent 
* to the basics of SQL
* 
*/
use Empiric\Libraries\General;
use Empiric\Libraries\Encrypt;
use Empiric\Libraries\XML;

class Abstraction {

	/** 
	 * @param obj the database object
	 * */
	public $db;

	/** 
	 * @param string path to the definitions database
	 * */
	private $dbpath;

	/** 
	 * @param int $wstime when writing if the file is locked, how long should the program wait to try to write again
	 */
	private $wstime;

	/** 
	 * @param int $wstime  when writing if the file is locked, how many times to try
	 */
	private $wsmax;

	/** 
	 * @param int $wscount 
	 */
	private $wscount = 0;


	/** 
	 * @param array $libs 
	 */
	private $libs = array();


	function __construct($params) {
		$this->dbpath = General::is_set($params,'dbpath','assets');
		$this->wstime = General::is_set($params,'wstime',3);
		$this->wsmax = General::is_set($params,'wsmax',10);
		$this->libs['enc'] = new Encrypt();
		$this->libs['xml'] = new XML();
		$this->db = $this->get_connection();
	}
	/** 
	 * get_connection
	 * @return object
	 * */
	function get_connection() {
		$file = $this->read();
		if(false === $this->read()) {
			return false;
		}
		$file = $this->libs['enc']->decrypt($file);
		return $this->libs['xml']->getObj($file);
	}
	/** 
	 * read
	 * @param string $data
	 * @return boolean
	 * */
	private function read() {
		if(false == file_exists($this->dbpath)) {
			// set error
			$GLOBALS['errors']->set_error_message(__METHOD__.' the requested file does not exist: '.$this->dbpath);
			return false;
		}
		return file_get_contents($this->dbpath);
	}
	/** 
	 * write
	 * @param string $data this should be encrypted and compressed. its not this operations responsability
	 * @return boolean
	 * */
	private function write($data) {

		if(false == is_string($data)) {
			// set error
			$GLOBALS['errors']->set_error_message(__METHOD__.' expected string. received '.gettype($data));
			return false;
		}
		// 
		while(false == @file_put_contents($this->dbpath, $data)) {
			$this->wscount++;
			if($this->wscount > $wsmax) {
				// set error
				return false;
			}
			sleep($this->wstime);
			return $this->write($string);
		}
		//
		return file_put_contents($this->dbpath, $data);
	}
}