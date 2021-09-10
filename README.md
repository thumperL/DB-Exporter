# Database to Spreadsheet Exporter

This script was built to export database for PHP-back sites to export the data into excel spreadsheet.

The script is now using 'PHP Spreadsheet', and has been tested with PHP v8.0.

The problem I was facing was that I need to export a few DBs into excel spreadsheet, and each of them has around 60gbs and around 30 tables.  Normal db dumps with GUIs to CSVs just won't work.

This script is meant to partially solve my problem by able to export smaller DB with each of the table in a sheet, OR exporting tables within a DB in batch.


## Features

1. If the database is not too large
  - Execute the [data_excel_export.php] script to dump out the whole

2. If the database is large
  - Execute [data_excel_export_entry_3tbl.php?first_run=true&start_tbl=tables&batch_size=3]
    - This shows you list of all the tables within your set
    - You can then select each batch of tables to be exported
  - Execute [data_excel_export_entry_3tbl.php?start_tbl=TABLE_NAME_HERE&batch_size=3]
    - Repeat this process to export selected tables.  You can change your batch size to up to 10.

3. If tables were ran in batch, then select the sheets -> move/copy -> to the main sheet

## Prerequisites

1. [PHPSpreadsheet] (https://github.com/PHPOffice/PhpSpreadsheet)
2. [PHP] tested to v8.0

## Disclaimer
Use this repo at your own risk, I am not responsible for any issue it may cause.


## Contributor

> [Thumper](https://github.com/thumperL)
