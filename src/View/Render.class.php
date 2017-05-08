<?php
namespace Empiric\View;
/**
 *  
 * Render
 *  
 * @author Jeremy Heminger <j.heminger13@gmail.com>
 * @version 1.0.0.1
 *
 * */
class Render
{
	public static function cmd($message,$die=false) {
		print "\n".$message."\n";
		if(true === $die)die();
	} 
	public static function html($message,$die=false) {
		echo $message;
	}
	public static function json($message,$success=true,$die=true) {
		echo json_encode(array('success'=>$success,$message));
		if(true === $die)die();
	}
}