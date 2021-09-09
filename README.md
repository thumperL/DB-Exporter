# Database to Spreadsheet Exporter

This script was built to export database for PHP-back sites to export the data into excel spreadsheet.  This script used 'PHPExcel' (https://github.com/PHPOffice/PHPExcel).

The problem I was facing was that I need to export a few DBs into excel spreadsheet, and each of them has around 60gbs and around 30 tables.  Normal db dumps with GUIs to CSVs just won't work.


## Features

1. If the database is not too large, use the [data_excel_export.php] script to dump out the whole 
2. If the database is too large, run the [data_excel_export_entry_3tbl.php?start_tbl=TABLE_NAME_HERE&batch_size=10]
3. Run in batch, then select the sheets -> move/copy -> to the main sheet, select (at the end)

## Prerequisites

1. [PHPExcel] (https://github.com/PHPOffice/PHPExcel)
2. [PHP] tested to v5.6 only

## Disclaimer
Use this repo at your own risk, I am not responsible for any issue it may cause.


## Contributor

> [Thumper](https://github.com/thumperL)
