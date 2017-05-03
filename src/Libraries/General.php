<?php
namespace Emperic\Libraries;
/**
 *  
 *  General
 *  
 *  This represents a list of general methods I have gathered over the years that I find useful...some more than others
 *  some are outright depricated or just plain wrong...I remove stuff from time-to-time
 *  
 *  @author By Jeremy Heminger <j.heminger@061375.com>
 *  @copyright © 2013 to present 
 *
 * */
class General
{
	/**
	 * get a clean query string key
	 * @param string $key
	 * @param string
	 * @param bool
	 * @return string
	 * */
    public static function get_variable($key,$else='',$uri = false)
    {
        if(false == defined("GET_STRING")){
            parse_str(($_SERVER['QUERY_STRING'] ? $_SERVER['QUERY_STRING'] : ''), $_GET);
            define("GET_STRING",true);
        }
	$GET = self::cleanSuperGlobal($_GET,'clean_get');
        $return = isset($GET[$key]) ? $GET[$key] : $else;
	if(false !== $uri) {
	    if(false == is_string($return))return $else;
	    if(strpos($return,'/') === false)return array($return);
	    return explode('/',$return);
	}
	return $return;
    }
	/**
     * clean super globals
     * @param array $elem
     * @param string $globalkey
     * @return array
     *  */
    public static function cleanSuperGlobal($elem,$globalkey='') {
	    if(isset($GLOBALS[$globalkey]))return $GLOBALS[$globalkey];
	    if(false == is_array($elem)) 
		    $elem = htmlentities($elem,ENT_QUOTES,"UTF-8"); 
	    else 
		    foreach ($elem as $key => $value) 
			    $elem[$key] =self::cleanSuperGlobal($value); 
	    return $elem; 
    } 
    /**
     * gets a clean post string key
     * @param string $key
     * @param string $else
     * @param bool $bool if the expected result type is boolean
     * @param bool $die if the expected value is not mnet or empty should the program die
     * @param string $redirect if set the operation will redirect the user to $redirect
     * @param string $message a redirect message
     * @return mixed
     *  */
    public static function post_variable($key,$else='',$bool=false,$die=false,$redirect='',$message='')
    {
		$POST = self::cleanSuperGlobal($_POST,'clean_post');
        if($bool == false){
            $return = isset($POST[$key]) ? $POST[$key] : $else;
        }else{
            $return = isset($POST[$key]) ? 1 : 0;
        }
        if($return == '')
        {
            if($die == false){
                return $return;
            }else{
				if($redirect == ''){
					die($message);
				}else{
					self::Location($redirect);
				}
            }
        }
        return $return;
    }
    /**
     * gets a query and if false falls to POST
     * NOTE: *** this is bad practice and should be removed ***
     * @param string array key
     * @param string else
     * @return mixed
     *  */
    public static function get_query($var,$else='')
    {
        $return = self::get_variable($var,'');
        if($return == '')
        {
            return self::post_variable($var,$else);
        }
        return $return;
    }
    /**
     * this is the same as self::get_query but it uses the $_REQUEST super global
     * NOTE: *** this is bad practice and should be removed ***
     * @param string $key
     * @param string
     * @return mixed
     *  */
    public static function get_request($key,$else=''){
		$REQUEST = self::cleanSuperGlobal($_REQUEST,'clean_request');
        return isset($REQUEST[$key]) ? $REQUEST[$key] : $else;
    }
    /**
     * shortcut to ternary finding of an array key
     * @param array $array
     * @param string $key
     * @param string $default
     * @return mixed
     *  */
    public static function getFunctionParam($array,$key,$default=''){
        return isset($array[$key]) ? $array[$key] : $default;
    }
    /**
     * shortcut to ternary finding of an array key (same as getFunctionParam witha shorter name)
     * @param array $array
     * @param string $key
     * @param string $default
     * @return mixed
     *  */
    public static function is_set($array,$key,$default=''){
        return isset($array[$key]) ? $array[$key] : $default;
    }
    /**
     * shortcut to ternary catch for the PHP defined function
     * @param string the CONSTANT to check if defined
     * @param string $else
     * @return bool
     *  */
    public static function is_defined($variable,$else = ''){
        return defined($variable) ? $variable : $else;
    }
    /**
     * recursively finds a set key in the super global $_SESSION
     * NOTE: *** this could be reworked to use self::recurse_array_get to allow larger arrays
     * @param array $key
     * @param string $else
     * @return mixed
     *  */
    public static function get_session($key,$else=''){
        // init session if false
		if (session_id() === '' && headers_sent() == false)session_start();
		if(is_array($key) == false){
			return isset($_SESSION[$key]) ? $_SESSION[$key] : $else;
		}else{
			switch(count($key)){
				case 2:
					return isset($_SESSION[$key[0]][$key[1]]) ? $_SESSION[$key[0]][$key[1]] : $else;
				break;
				case 3:
					return isset($_SESSION[$key[0]][$key[1]][$key[2]]) ? $_SESSION[$key[0]][$key[1]][$key[2]] : $else;
				break;
				case 4:
					return isset($_SESSION[$key[0]][$key[1]][$key[2]][$key[3]]) ? $_SESSION[$key[0]][$key[1]][$key[2]][$key[3]] : $else;
				break;
				default:
				return $else;
			}
		}
    }
    /**
     * set a session at key [x][y][z]
     * NOTE: *** this might be made to allow recurse_array_get (similar)
     * @param array $key
     * @param string $value the value to add to the array
     * @return void
     *  */
    public static function set_session($key,$value) {
        // init session if false
		if (session_id() === '' && headers_sent() == false)session_start();
		
		
		if(is_array($key) == false){
			$_SESSION[$key] = $value;
		}else{
			switch(count($key))
			{
				case 2:
					$_SESSION[$key[0]][$key[1]] = $value;
				break;
				case 3:
					$_SESSION[$key[0]][$key[1]][$key[2]] = $value;
				break;
				case 4:
					$_SESSION[$key[0]][$key[1]][$key[2]][$key[3]] = $value;
				break;
			}
		}
    }
    /**
     *	
     *  */
    public static function dateToMonthsCount($date_from) {
        $current_date = date('Y-m-d H:i:s',strtotime('now')); //current date
        $diff = strtotime($current_date) - strtotime($date_from);
        $months = floor(floatval($diff) / (60 * 60 * 24 * 365 / 12));
        return $months;
    }
    /**
     * returns true if date is format Y/m/d
     * @param string
     * return bool
     *  */
    public static function checkDateFormat($date) {
        if(preg_match('/^[0-9]{4}\/[0-9]{2}\/[0-9]{2}$/', $date)){
            return true;
        }else{
            return false;
        }
    }
    /**
     * converts an object into a simple array
     * @param object $obj
     * return array
     *  */
    public function simpleObjectToArray($obj = null) {
        if(empty($obj))return;
        $arrObj = is_object($obj) ? get_object_vars($obj) : $obj;
        $arr = array();
        foreach ($arrObj as $key => $val) {
                $val = (is_array($val) || is_object($val)) ? self::simpleObjectToArray($val) : $val;
                $arr[$key] = $val;
        }
        return $arr;
    }
    /**
     * creates a message from the message.yml file
     * @param string
     * @param array
     * @return string
     * */
    public static function message($level,$keys,$sprint)
    {
		$return = 'Gluon '.ucfirst($level).': ';
		$return .= \Gluon\Libraries\Cache::get_cache_byfile('messages.yml.php',$keys);
		foreach($sprint as $k => $v) {
			$return = str_replace('[#'.$k.'#]',$v,$return);
		}
		return $return."\n";
    }
    /**
     * recurse_array_get
     * @param array $array
     * @param mixed $keys - the path to the array key of interest
     * 	      example: to find $array['a']['b']['c'] use: recurse_array_get($array,array('a','b','c'));
     * @param string $default
     * @return mixed (array, bool)
     * */
    public static function recurse_array_get($array,$keys,$default = '')
    {
		//
		if(false == is_array($keys))
			return self::is_set($array,$keys,$default);
		$result = '';
		foreach($keys as $i => $key)
		{
			if(isset($array[$key])) {
				if(is_array($array[$key])) {
					$result = self::recurse_array_get($array[$key],$keys);
					if(!is_array($result)) {
						return $result;
					}
				}else{
					return $array[$key];
				}
			}
		}
		return $default;
    }
    /**
     * str_replace the first result only
     * @param string $from
     * @param string $to
     * @param string $subject
     * @return string
     * */
    public static function str_replace_first($from, $to, $subject)
    {
        $from = '/'.preg_quote($from, '/').'/';

        return preg_replace($from, $to, $subject, 1);
    }
    /**
     * recurse through specified folder search for specified file type
     * @param string $dir
     * @param string $ext
     * @param mixed $return the first time through this array needs to assigned
     * @return array
     * */
    public static function recurse_get_files($dir,$ext = '.php',$return = false)
    {
        if(false === $return)
            $return = array();
        $Directory = new \RecursiveDirectoryIterator($dir);
        $Iterator = new \RecursiveIteratorIterator($Directory);
        $objects = new \RegexIterator($Iterator, '/^.+\\'.$ext.'$/i', \RecursiveRegexIterator::GET_MATCH);
        foreach($objects as $name => $object){
            $return[str_replace('.html.php','',basename($name))] = $name;
        }
        return $return;
    }
    /**
     * @param string $url
     * @param array $post
     * @param bool $code
     * @param array $header
     * @return string
     * */
    public static function simpleCurl($url,$post,$code = false,$header = array('Expect:'))
    {
	$postValuesString = '';
        foreach($post as $var => $val) {
	    if(is_array($val)) {
		foreach($val as $k => $v) {
		    if(strlen($postValuesString))$postValuesString.= "&";
		    $postValuesString.= $var . "[".$k."]=" . urlencode($v);   
		}
	    }else{
		if(strlen($postValuesString))$postValuesString.= "&";
		$postValuesString.= $var . "=" . urlencode($val);
	    }
	}
	$ch = curl_init(); 
	// set url 
	curl_setopt($ch, CURLOPT_URL, $url); 
	//return the transfer as a string 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
	curl_setopt($ch, CURLOPT_POST,1);
	curl_setopt($ch, CURLOPT_POSTFIELDS,$postValuesString);
	curl_setopt($ch, CURLOPT_HTTPHEADER,$header);
	// $output contains the output string 
	$output = curl_exec($ch);
	if (true === $code) {
	    return curl_getinfo($ch, CURLINFO_HTTP_CODE);
	}
	// close curl resource to free up system resources 
	curl_close($ch);
            
        return $output;
    }
}