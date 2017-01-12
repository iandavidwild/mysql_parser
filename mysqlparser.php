<?php

/**
 * Spools the contents of an array (the buffer) to the specified file. Note that we are using the disk as a temporary
 * file store. 
 * 
 * @param string` $filename
 * @param array $buffer
 */
function dumpbuffer($filename, array $buffer) {
    $fh = fopen("./output/".$filename, "w");
            
    foreach($buffer as $element) {
        fputs($fh, $element);
    }
            
    fclose($fh);
}

/**
 * Parses the header out of the SQL file and spools this to disk
 * 
 * @param unknown $fh - handle to SQL file
 */
function getheader($fh) {
    $header = array();
    $gotheader = false;
    
    $tablename = null; // name of the table that we have encountered next
    $matches = array();
    
    while (!feof($fh) && !$gotheader) // Loop til end of file.
    {
        // Get line from input file
        $buffer = fgets($fh, 4096);
        
        if($buffer != false) {
            // Have we got the header?
            
            $gotheader = preg_match("/Table structure for table `([^#]+)`/", $buffer, $matches);
        
            if(!$gotheader) {
                // Buffer up the text
                $header[] = $buffer;
            } else {
                dumpbuffer("header.tmp", $header);
                $tablename = $matches[1];
            }
        }// if($buffer != false)
    }// while (!feof($handle) && !$gotheader)
        
    return $tablename;
}

/**
 * Recursive function that extracts all tables out of the file.
 * 
 * @param unknown $fh
 * @param unknown $details
 */
function parsetable($fh, $tablename) {
    $table = array();
    
    $gotnewtable = false;
    $nexttable = null; // name of the table that we have encountered next
    
    $matches = array();
        
    while (!feof($fh) && !$gotnewtable) // Loop til end of file.
    {
        // Get line from input file
        $buffer = fgets($fh, 4096);
    
        if($buffer != false) {
            // Have we got the header?
            $gotnewtable = preg_match("/Table structure for table `([^#]+)`/", $buffer, $matches);
    
            if(!$gotnewtable) {
                // Buffer up the text
                $table[] = $buffer;
            } else {
                $filename = $tablename . '.tmp';
                dumpbuffer($filename, $table);
                $nexttable = $matches[1];
            }
        }// if($buffer != false)
    }// while (!feof($handle) && !$gotnewtable) 

    return $nexttable;
}

ini_set('memory_limit', '-1');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Open source file for reading
$handle = fopen("./input/input.sql", "r");

if($handle) {
    // Firstly we need the header
    $buffer = getheader($handle);
    
    while(!feof($handle) && isset($buffer)) {
        // Now parse out tables
        $buffer = parsetable($handle, $buffer);
    }
    
    // Close source file
    fclose($handle);
}
?>