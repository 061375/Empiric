<?php
namespace Empiric\Libraries;
/**
 *  
 *  ErrorHandler
 *  @author By Jeremy Heminger <j.heminger@061375.com>
 *  @copyright © 2013 to present 
 *
 * */
class ErrorHandler 
{
	
	protected $errors;
	
	function __construct() {
		//parent::__construct(); 
	}
    /**
     * Get Error messages
     *
     * @return array
     */
    public function get_error_message()
    {
        if (count($this->errors) > 0) {
            $tmp = $this->errors;
            $this->errors = array();
            return $tmp;
        }
        return array();
    }
    // --------------------------------------------------------------------
    /**
     * Set Error messages
     * @param string $message
     * @return array
     */
    public function set_error_message($message)
    {
        if ($message != '') {
            $this->errors[] = $message;
        }
    }
	
    // --------------------------------------------------------------------
    /**
     * Has Error
     * @return array
     */
    public function has_error()
    {
        if (isset($this->errors)) {
            if (count($this->errors) > 0) {
                return true;
            }
        }
        return false;
    }
    // --------------------------------------------------------------------
    /**
     * Clear Error
     * @return array
     */
    public function clear_error()
    {
        $this->errors = array();
    }
    /**
     * Gathers errors and converts them to XML to be returned to the user
     * @param bool $echo
     * @param bool $cmd
     * @return void
     */
    public function display_errors($echo = false,$cmd = false)
    {
        $this->errors = $this->get_error_message();
        if (false === $echo) {
		      header('application/json');
                $result = array(
                        'success' => 0,
                        'errors' => $this->errors
                );
                echo json_encode($result);
                die();
        } else {
            foreach( $this->errors as $row) {
        		if (false === $cmd) {
        			echo '<p>'.$row.'</p>';
        		} else {
        			echo $row."\n";
        		}
            }
            exit();
        }
    }
	/**
	 * @return void
	 * */
    public function log_errors()
    {
		$error_log = isset($this->errors) ? $this->errors : 'error_log';
		$this->errors = $this->get_error_message();
		if(file_exists($error_log)) {
			$chk = file_get_contents($error_log);
		}else{
			$chk = '';
		}
		//100000
		if (strlen($chk) > 5000) {
			file_put_contents($error_log.'_'.date('Ymd'),$chk);
			foreach( $this->errors as $row) {
				file_put_contents($error_log,date('Y-m-d H:i:s').' '.$row."\n",FILE_APPEND);
			}
		}else{
			foreach( $this->errors as $row) {
				file_put_contents($error_log,date('Y-m-d H:i:s').' '.$row."\n",FILE_APPEND);
			}
		}
    }
}