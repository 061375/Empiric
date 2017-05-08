<?php 
namespace Empiric;
/** 
 *  
 * 
 * 
 * */
use Symfony\Component\Yaml\Yaml;
class Core {
	public static function autoload() {	
		// autoload 
		$ext = '.class.php';

		$return = array();
		$Directory = new \RecursiveDirectoryIterator('src');
		$Iterator = new \RecursiveIteratorIterator($Directory);
		$objects = new \RegexIterator($Iterator, '/^.+\\'.$ext.'$/i', \RecursiveRegexIterator::GET_MATCH);

		// loop files and include
		foreach ($objects as $object) {
			foreach ($object as $file) {
				require_once($file);
			}
		}
	}
	public static function run()
	{
		$logic = new Controller\Logic();
	}
}