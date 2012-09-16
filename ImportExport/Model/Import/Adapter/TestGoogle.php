<?php

/**
 * Quick and dirty little test wrapper for the Google import class.
 * @author aschroder
 */

set_include_path(get_include_path() . PATH_SEPARATOR . getcwd());
require_once 'app/Mage.php';
Varien_Profiler::enable();
Mage::setIsDeveloperMode(true);
ini_set('display_errors', 1);


/**
 *  helper class to mock out the 'real' csv functionality
 */
class Mage_ImportExport_Model_Import_Adapter_MockGoogle extends Mage_ImportExport_Model_Import_Adapter_Google {
	protected function _init() {
		// do nothing - we're testing - keep the FINAL constructor happy.
		$this->_colNames = array("x","y");
	}
}

Mage::app('default');

$google = new Mage_ImportExport_Model_Import_Adapter_MockGoogle(__FILE__);

// Check null lines - should get no new lines
$newLines = $google->_getNewLines(null);
assert(sizeof($newLines)==0);

// normal lines - should get no new lines
$newLines = $google->_getNewLines(array("value","value2"));
assert(sizeof($newLines)==0);

// simple case, get 2 new lines
$newLines = $google->_getNewLines(array("value","value2||value3"));
assert(sizeof($newLines)==2);
assert($newLines[1][1] == "value3");


// more complex case, get 4 new lines
$newLines = $google->_getNewLines(array("value","value2||value3","value4||value5||value6||value7","value8","value9||value10||value11"));
assert(sizeof($newLines)==4);
assert($newLines[0][0] == "value0");
assert($newLines[1][1] == "value3");
assert($newLines[3]20] == "value7");
assert($newLines[2][4] == "value10");

?>