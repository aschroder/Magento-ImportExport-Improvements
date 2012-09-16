<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * 
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category   Mage
 * @copyright  Copyright (c) 2012 Ashley Schroder
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Import a .csv file from GoogleDocs given a url in a 'import.csv.google' file.
 * 
 * a .google file containing just your published CSV url like:
 * 
 * http://url-to-your-google-docs-csv
 * 
 * 
 */
class Mage_ImportExport_Model_Import_Adapter_Google extends Mage_ImportExport_Model_Import_Adapter_Csv {
	
	const SPECIAL_FILTER_STRING           = "||";
	
	/**
	* 
	* Download the file in the source, and then update the source 
	* to be the downloaded file before firing the usual _init()
	*
	* @return Mage_ImportExport_Model_Import_Adapter_Abstract
	*/
	protected function _init() {
		
		// Read the url from the .google file
		$this->_fileHandler = fopen($this->_source, 'r');
		$csvUrl = fgets($this->_fileHandler);
		
		// fetch the actual csv and save it into a _downloaded.csv file
		$csv = file_get_contents(trim($csvUrl));

		$downloadedFileName = $this->_source."_downloaded.csv";
		file_put_contents($downloadedFileName, $csv);
		
		// Translate the custom format to the original format
		
		$file = fopen($downloadedFileName, 'r');
		$filteredFileName = $this->_source."_filtered.csv";
		$filteredFile = fopen($filteredFileName, 'w');
		
		while (($line = fgetcsv($file)) !== FALSE) {
			
			$newLines = $this->_getNewLines($line);

			// if we got new lines, write them, or write the existing line
			if (sizeof($newLines) == 0) {
				fputcsv($filteredFile, $line);
			} else {
				// put the new lines instead
				foreach ($newLines as $newLine) {
					fputcsv($filteredFile, $newLine);
				}
			}
		}
		
		fclose($file);
		fclose($filteredFile);
		$this->_source = $filteredFileName;
		return parent::_init();
	}
	
	
	public function _getNewLines($line) {
		
		$newLines = array();
		
		if ($line == null) {
			return $newLines;
		}
		
		$cellCount = 0;
		$numCells = sizeof($line);
			
		foreach ($line as $cell) {
		
			if (strpos($cell, $this::SPECIAL_FILTER_STRING) !== false) {
					
				// this line is a special one
				$splitCell = explode($this::SPECIAL_FILTER_STRING, $cell);
					
				$splitCount = 0;
				foreach ($splitCell as $cellPart) {
		
					if (!isset($newLines[$splitCount])) {
						// the first newLine is a copy of the current line, all others are empty
						$newLines[$splitCount] = ($splitCount == 0 ? $line : array_fill(0, $numCells, ""));
					}
		
					// set the cell of the line to the split value
					$newLines[$splitCount][$cellCount] = $cellPart;
					$splitCount++; // next split
				}
			}
		
			$cellCount++;
		}
		
		return $newLines;
	}

}
