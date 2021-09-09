<?php
error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);
date_default_timezone_set('Australia/Melbourne');
ini_set('memory_limit', '-1');

$servername = "";
$username = "";
$password = "";
$dbname = "";
$sheet_data = array();

// Create connection
$conn = new mysqli($servername, $username, $password);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$tables_sql = "SELECT * FROM information_schema.tables a WHERE a.table_schema = '$dbname' ORDER BY a.TABLE_NAME";
$tables_result = $conn->query($tables_sql);

$conn->select_db($dbname);


/** Include PHPExcel */
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

// Create new PHP Spreadsheet object
$spreadsheet = new Spreadsheet();
// Set document properties
$spreadsheet->getProperties()->setTitle("$dbname");

$sheet_count = 0;

while ($table_row = $tables_result->fetch_assoc()) {
    // Used to set sheet index
    $table_name = $table_row['TABLE_NAME'];

    // Add new sheet
    $objWorkSheet = $spreadsheet->createSheet($sheet_count);

    // Set all to text, avoid weird data type display
    $objWorkSheet->getStyle('A1')
        ->getNumberFormat()
        ->setFormatCode(
            \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT
        );

    // Set first row bold
    $objWorkSheet->getStyle('1:1')->getFont()->setBold(true);
    $objWorkSheet->getStyle('2:2')->getFont()->setBold(true);
    $objWorkSheet->getStyle('3:3')->getFont()->setBold(true);

    // Add Table Name to first row
    $objWorkSheet->setCellValue('A1', 'Table Name: '.$table_name);

    // Add data from inner array
    if ($data = table_data_to_arr($conn, $table_name)) {
        $objWorkSheet->fromArray($data, NULL, 'A3');
    }

    // Set sheeet title
    $sheet_name = substr($table_name, strlen($table_name) - 31, 31);
    $objWorkSheet->setTitle("$sheet_name");
    

    // Release Memory
    unset($table_name);
    unset($sheet_name);
    unset($data);

    $sheet_count += 1;
}
mysqli_free_result($tables_result);

// Remove the last empty sheet created
$spreadsheet->removeSheetByIndex($sheet_count);



$conn->close();
$writer = new Xlsx($spreadsheet);
$filename = sprintf('%s_%s', $dbname, date('Y-m-d-H-i'));
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="'.urlencode($filename).'.xlsx"');
header('Cache-Control: max-age=0');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
header('Pragma: public'); // HTTP/1.0

$writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
$writer->save('php://output');
exit;


function table_data_to_arr($conn, $sTablename = '')
{
    // $conn       -> Connection variable
    // $sTablename -> Name of Database table from which records need to be exported
    $data = array();

    // Get table Data
    $sSelectQry_result = mysqli_query($conn, "SELECT * FROM $sTablename");

    // If empty set, return false
    if (!$sSelectQry_result) {
        return false;
    }

    // Heading will be in the first row, loop through all col and get all headings
    for ($i = 0; $i < $sSelectQry_result->field_count; $i++) {
        $fieldinfo = $sSelectQry_result->fetch_field_direct($i);
        $sHeading = $fieldinfo->name;
        $data[0][] = $sHeading;
    }

    // Loop through each row
    $i = 1;
    while ($sRow = mysqli_fetch_assoc($sSelectQry_result)) //Fetch a result row as an associative array, a numeric array, or both
    {
        foreach ($sRow as $ea_val) {
            $data[$i][] = $ea_val;
        }
        $i += 1;
    }
    
    // Release Memory
    mysqli_free_result($sSelectQry_result);
    unset($fieldinfo);
    unset($sHeading);

    return $data;
}