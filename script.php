<?php

$databaseHost = "localhost";
$databaseName = "test";
$databaseTable = "data";
$databaseUsername ="dummy";
$databasePassword = "dummy";
$fieldSeparator = ",";
$lineSeparator = "\n";
$csvFile = "csv-filename.csv";

/********************************/
/* Would you like to add an ampty field at the beginning of these records?
/* This is useful if you have a table with the first field being an auto_increment integer
/* and the csv file does not have such as empty field before the records.
/* Set 1 for yes and 0 for no. ATTENTION: don't set to 1 if you are not sure.
/* This can dump data in the wrong fields if this extra field does not exist in the table
/********************************/
$addauto = 0;
/********************************/

/* Would you like to save the mysql queries in a file? If yes set $save to 1.
/* Permission on the file should be set to 777. Either upload a sample file through ftp and
/* change the permissions, or execute at the prompt: touch output.sql && chmod 777 output.sql
/********************************/
$save = 1;
$outputFile = "csv-output.sql";
/********************************/

if (!file_exists($csvFile)) {
        echo "File not found. Make sure you specified the correct path.\n";
        exit;
}

$file = fopen($csvFile,"r");

if (!$file) {
        echo "Error opening data file.\n";
        exit;
}

$size = filesize($csvFile);

if (!$size) {
        echo "File is empty.\n";
        exit;
}

$csvContent = fread($file,$size);

fclose($file);

$con = @mysql_connect($databaseHost,$databaseUsername,$databasePassword) or die(mysql_error());
@mysql_select_db($databaseName) or die(mysql_error());

$lines = 0;
$queries = "";
$linearray = array();

foreach(split($lineSeparator,$csvContent) as $line) {

        $lines++;

        $line = trim($line," \t");

        $line = str_replace("\r","",$line);

        /************************************
        This line escapes the special character. remove it if entries are already escaped in the csv file
        ************************************/
        $line = str_replace("'","\'",$line);
        /*************************************/

        $linearray = explode($fieldseparator,$line);

        $lineMysql = implode("','",$linearray);

        if($addauto)
                $query = "insert into $databaseTable values('','$lineMysql');";
        else
                $query = "insert into $databaseTable values('$lineMysql');";

        $queries .= $query . "\n";

        @mysql_query($query);
}

@mysql_close($con);

if ($save) {

        if (!is_writable($outputFile)) {
                echo "File is not writable, check permissions.\n";
        }

        else {
                $file2 = fopen($outputFile,"w");

                if(!$file2) {
                        echo "Error writing to the output file.\n";
                }
                else {
                        fwrite($file2,$queries);
                        fclose($file2);
                }
        }

}

print "Found a total of $lines records in this csv file.\n"; 
//Exit the script
exit();

?>
