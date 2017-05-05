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
* to the basics of SQL. I realize this is at best un-neccessary. But, really its about the exercise...
* I simply want to do it. 
* 
*/
use Empiric\Libraries\General;
use Empiric\Libraries\Encrypt;
use Empiric\Libraries\XML;

class Abstraction {

	/** 
	 * @param obj the database object
	 * */
	protected $db;

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
		$this->db->actions = new Ab_Actions();
		$this->db->locations = new Ab_Locations();
		$this->db->operations = new Ab_Operations();
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
		$return = $this->libs['xml']->getObj($file);
		if(false === isset($return->firstChild->nodeName)) {
			// set error
			$GLOBALS['errors']->set_error_message(__METHOD__.' database appears to be malformed.');
			return false;
		}
		$return->root = $return->firstChild->nodeName;
		return $return;
	}
//$res = $xpath->query("//book/price[.>'40']/parent::*");  // select * from book where price > 40
//$res = $xpath->query("//book[price<35]/author");  // select author from book where price < 35 
//$res = $xpath->query("//book[contains(title,\"Mid\")]"); // select * from book where title like '%Mid%'
//$res = $xpath->query("//book/price[.>5 and .<50]/parent::*");  // select * from book where price > 5 and price < 50
	public function query($q) {
		$match = array(
			'actions' => array('select','delete','update','insert','create','drop','explain','show'),
			'locations'=>array('table','where','from','into'),
			'operators'=>array('and','or','<','>','=','!','>=','<=','!=')
		);

		$this->db->query = array();

		// get the quoted content first that might contain spaces etc
		preg_match_all("/'[^'\\\\]*(?:\\\\.[^'\\\\]*)*'/s",$q,$m);
		if(isset($m[0]) AND (count($m[0]) > 0)) {
			foreach ($m[0] as $key => $value) {
				$q = str_replace_first($value,'[{'.$key.'}]',$q);
			}
		}
		
		// 
		$statements = explode(" ",$q);
		foreach ($statements as $key => $s) {
			$s = str_replace('`','',$s);
			// find actions
			foreach ($match['actions'] as $m) {
				if($m == trim($s)) {
					$query['actions'][$key] = $m;
				}
			}
			// find locations
			foreach ($match['locations'] as $m) {
				if($m == trim($s)) {
					$query['locations'][$m] = $key;
				}
			}
			// find operators
			foreach ($match['operators'] as $m) {
				if($m == trim($s)) {
					$query['operators'][$key] = $m;
				}
			}
			if((strpos('[{',$s) !== false) AND (strpos('}]',$s) !== false)) {
				$k = str_replace('[{',$s);
				$k = str_replace('}]',$k);
				$query['strings'][$key] = isset($m[0][$k]) ? $m[0][$k] : '';
			}
		}
		$this->db->query['all'] = $statements;

		foreach($this->db->query['actions'] as $action) {
			$results[] = $this->db->actions->router($action);
		}
		if($GLOBALS['errors']->has_error()) {
			return false;
		}
		return $results;
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
/** 
 * 
 * 
 * */
class Ab_Actions extends Abstraction {
	//
	/* @param array */
	private $query;
	//
	function __construct() {
	}
	/** 
	 * @param string $method
	 * @return mixed
	 * */
	function router($method) {
		if(false == method_exists($this, $method)) {
			$GLOBALS['errors']->set_error_message('you have an error in your query near '.$method.'; please refer to he documentation');
			return false;	
		}
		$this->query = $this->db->query;
		return $this->$method();
	}
	function select() {
		$q = '';
		if(false == isset($this->query['locations']['from'])) {
			$GLOBALS['errors']->set_error_message('you have an error in your query near SELECT expects a FROM clause; please refer to he documentation');
			return false;	
		}
		// get from what table
		$q = $this->libs['locations']->router('from');
		if(false === $q) return false;
		
		// determine search criteria

		// determine what columns to return

		// return thos columns


	}
}
/** 
 * 
 * 
 * */
class Ab_Locations extends Abstraction {
	//
	/* @param array */
	private $query;
	//
	function __construct($query) {

	}
	/** 
	 * @param string $method
	 * @return mixed
	 * */
	function router($method) {
		if(false == method_exists($this, $method)) {
			$GLOBALS['errors']->set_error_message('you have an error in your query near '.$method.'; please refer to he documentation');
			return false;	
		}
		$this->query = $query;
		return $this->$method();
	}
	/** 
	 * @return string
	 * */
	function from() {
		$root = $this->root;
		$query = $this->query;
		if(false == isset($query['all'][(int)$query['locations']['from']+=1])) {
			// table not found error
			$GLOBALS['errors']->set_error_message('you have an error in your query; table not found');
			return false;	
		}
		if(false == isset($this->db->$root->$query['all'][(int)$query['locations']['from']+=1])) {
			// table not found error
			$GLOBALS['errors']->set_error_message('you have an error in your query; table '.$query['all'][(int)$query['locations']['from']+=1].' not found');
			return false;
		}
		return $root.'/'.$this->db->$root->$query['all'][(int)$query['locations']['from']+=1];
	}
}
/** 
 * 
 * 
 * */
class Ab_Operators extends Abstraction {
	//
	/* @param array */
	private $query;
	//
	function __construct() {
	}
	/** 
	 * @param string $method
	 * @return mixed
	 * */
	function router($method) {
		if(false == method_exists($this, $method)) {
			$GLOBALS['errors']->set_error_message('you have an error in your query near '.$method.'; please refer to he documentation');
			return false;	
		}
		$this->query = $query;
		return $this->$method();
	}
}