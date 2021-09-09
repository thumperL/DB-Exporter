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
require_once dirname(__FILE__) . '/Classes/PHPExcel.php';
// Create new PHPExcel object
$objPHPExcel = new PHPExcel();
// Set document properties
$objPHPExcel->getProperties()->setCreator("Shaun Lin")->setTitle("$dbname");

$sheet_count = 0;

while ($table_row = $tables_result->fetch_assoc()) {
    // Per table data
    // $sheet_data[$table_row['TABLE_NAME']] = table_data_to_arr($conn, $table_row['TABLE_NAME']);
    // Used to set sheet index
    $table_name = $table_row['TABLE_NAME'];

    // Add new sheet
    $objWorkSheet = $objPHPExcel->createSheet($sheet_count);

    // Set all to text, avoid weird data type display
    $objWorkSheet->getStyle('A1')
        ->getNumberFormat()
        ->setFormatCode(
            PHPExcel_Style_NumberFormat::FORMAT_TEXT
        );

    // Set first row bold
    $objWorkSheet->getStyle('1:1')->getFont()->setBold(true);

    // Add data from inner array
    if ($data = table_data_to_arr($conn, $table_name)) {
        $objWorkSheet->fromArray($data, NULL, 'A1');
    }

    // Set sheeet title
    $objWorkSheet->setTitle("$table_name");

    // Release Memory
    unset($table_name);
    unset($data);

    $sheet_count += 1;
}
mysqli_free_result($tables_result);

// Remove the last empty sheet created
$objPHPExcel->removeSheetByIndex($sheet_count);

// Redirect output to a client’s web browser (Excel5)
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="' . $dbname . '.xls"');
header('Cache-Control: max-age=0');
// If you're serving to IE 9, then the following may be needed
header('Cache-Control: max-age=1');

// If you're serving to IE over SSL, then the following may be needed
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
header('Pragma: public'); // HTTP/1.0

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
exit;

$conn->close();
die();


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