<?php
namespace Empiric\Libraries;
/**
 *
 * Command Line Handler 2016
 * @author Jeremy Heminger <j.heminger13@gmail.com>
 * @version 1.0.0.1
 *
 * */
class CMD
{
    /**
     * check if the program is running in a CMD environment
     * @param bool $bool if true and is not a CLI environment the process will die with error $error
     * @param string $error
     * @return void
     * */
    public static function iscmd($bool = true,$error = '')
    {
        if(true === $bool) {
            if (php_sapi_name() !== 'cli')
                die($error); // no message
        }
    }
    /**
     * determines what should be run from a CMD operation
     * @todo currently the returned array is associative...
     *       it might need to be keyed as ordered to call the methods in a specific order (maybe)
     * @param $method array
     * @return array
     * */
    public static function prep_cmd($methods = array()) {
        
        /**
         * @var array the built-in cmd argv variable
         * */
        global $argv;
        
        /**
         * @var array intructions to return to be ran by the program
         * */
        $return = array();
    
        // loop argv
        foreach($argv as $k => $flag) {
            
            // we don't need the current program
            if(0 == $k)continue;
            
            // loop methods
            foreach($methods as $method) {
                
                if(false == isset($method['name'])) {
                    die("\nno methods were specified\n");
                    // die
                }else{
                    if(false == isset($return[$method['name']]))
                        $return[$method['name']] = array();
                }
                
                if(false == isset($method['flag'])) {
                    die("\nno flags were specified");
                    // die
                }else{
                    if(false == isset($return[$method['name']]['flag']))
                        $return[$method['name']]['flag'] = false;
                }
                
                // check if the method expects input
                if(isset($method['vars'])) {
                    foreach($method['vars'] as $var) {
                        if(strpos($flag,$var) !== false) {
                            $key = str_replace('--','',$var);
                            $key = str_replace('=','',$key);
                            $return[$method['name']]['params'][$key] = str_replace($var,'',$flag);  
                        }
                    }// ./foreach($method)
                }
                
                // if the flag is an input then it can be skipped at this point
                if(strpos($flag,'=') !== false)continue;
                
                // remove the flag prefix
                $flg = str_replace('-','',$flag);
                
                $fl = strlen($flg);
       
                if($fl == 0) {
                    // if NO flags are specified then we can only assume that all should run
                    $return[$method['name']]['flag'] = true;   
                }else{
                    // loop each letter in the flag as a call to a method
                    for($i = 0; $i < $fl; $i++) {
                        if(substr($flg,$i,1) == trim($method['flag'])) {
                            $return[$method['name']]['flag'] = true;
                        }
                    }
                }// ./$fl
                
            }// ./foreach($methods)
            
        }// ./foreach($argv)
        return $return;
    }
    /**
     * loops and runs requested methods
     * @param array
     * @param mixed die if method not exist or give notice
     * @return void
     * */
    public static function run_methods($methods,$warning = false) {
        // 
        foreach($methods as $method => $params) {
            if(true === $params['flag']) {
                $p = (isset($params['params']) ? $params['params'] : '');
                if(function_exists($method)) {
                    $method($p);
                }else{
                    if(false === $warning) {
                        die("\nmethod does not exist\n");
                    }else{
                        print("\n".$warning." method: ".$method."\n");    
                    }
                }
            }
        }// ./foreach($methods)
    }
    /**
     * runs all the classes methods in order
     * @param array $methods
     * @param string $error
     * @return void
     * */
    public static function runall($methods,$error = '') {
        self::iscmd($error);
        $m = self::prep_cmd($methods);
        self::run_methods($m);
    }
}