<?php


namespace bfinlay\SpreadsheetSeeder\Writers\Database;

use bfinlay\SpreadsheetSeeder\Readers\Types\EmptyCell;
use bfinlay\SpreadsheetSeeder\SpreadsheetSeederSettings;
use bfinlay\SpreadsheetSeeder\Support\ColumnInfo;
use Composer\Semver\Semver;
use Doctrine\DBAL\Schema\Column;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class DestinationTable
{
    private $name;

    /**
     * @var bool
     */
    private $exists;

    /**
     * @var SpreadsheetSeederSettings
     */
    private $settings;

    /**
     * @var string[]
     */
    private $columns;

    /**
     * @var array
     */
    private $rows;

    /**
     *
     * See methods in vendor/doctrine/dbal/lib/Doctrine/DBAL/Schema/Column.php
     *
     * @var ColumnInfo[] $columnInfo
     */
    private $columnInfo;

    /**
     * @var array
     */
    private $primaryKey;

    public function __construct($name)
    {
        $this->name = $name;
        $this->settings = resolve(SpreadsheetSeederSettings::class);

        if ($this->exists() && $this->settings->truncate) $this->truncate();
        $this->loadColumns();
    }

    public function getName() {
        return $this->name;
    }

    public function exists() {
        if (isset($this->exists)) return $this->exists;
        $this->exists =  self::tableExists( $this->name );

        return $this->exists;
    }

    public static function tableExists($name)
    {
        return DB::getSchemaBuilder()->hasTable( $name );
    }

    public function truncate() {
        $ignoreForeign = $this->settings->truncateIgnoreForeign;

        if( $ignoreForeign ) Schema::disableForeignKeyConstraints();

        DB::table( $this->name )->truncate();

        if( $ignoreForeign ) Schema::enableForeignKeyConstraints();
    }

    protected function loadColumns() {
        if (! isset($this->columns)) {
            $this->columns = DB::getSchemaBuilder()->getColumnListing( $this->name );
            $connection = DB::getSchemaBuilder()->getConnection();

            $schemaBuilder = Schema::getFacadeRoot();
            if (method_exists($schemaBuilder, "getColumns")) {
                $columns = Schema::getColumns($this->name);
            }
            else {
                $columns = DB::getSchemaBuilder()->getConnection()->getDoctrineSchemaManager()->listTableColumns($this->name);
            }
            /*
             * Doctrine DBAL 2.11.x-dev does not return the column name as an index in the case of mixed case (or uppercase?) column names
             * In sqlite in-memory database, DBAL->listTableColumns() uses the lowercase version of the column name as a column index
             * In postgres, it uses the lowercase version of the mixed-case column name and places '"' around the name (for the mixed-case name only)
             * The solution here is to iterate through the columns to retrieve the column name and use that to build a new index.
             */
            $this->columnInfo = [];
            foreach($columns as $column) {
                $c = new ColumnInfo($column);
                $this->columnInfo[$c->getName()] = $c;
            }

            /*
             * The primaryKey is used by isPrimaryColumn() which is not currently used.   This may support future features like
             * ignoring the primary key column in determining if a row in a spreadsheet is empty and should be skipped.
             * getIndexes() is only available in Laravel 10.x and up.  TODO find alternative for older Laravel versions.
             */
            if (method_exists($schemaBuilder, "getIndexes")) {
                $indexes = DB::getSchemaBuilder()->getIndexes($this->name);
                $indexes = collect($indexes);
                $this->primaryKey = $indexes->first(function($value, $key) {
                    return $value['primary'] == true;
                });
            }
        }
    }

    public function getColumns() {
        return $this->columns;
    }

    public function isPrimaryColumn($columnName)
    {
        return $this->primaryKey['columns'][0] == $columnName;
    }

    public function getColumnType($name) {
        return $this->columnInfo[$name]->getType();
    }

    public function columnExists($columnName) {
        return in_array($columnName, $this->columns);
    }

    private function transformEmptyCellValue($columnName, $value) {
        if ($value instanceof EmptyCell) {
            $value = $this->defaultValue($columnName);
        }
        return $value;
    }

    private function transformDateCellValue($columnName, $value) {
        if (is_null($value)) {
            return null;
        }

        if ($this->isDateColumn($columnName)) {

            if (is_numeric($value)) {
                if (in_array($columnName, $this->settings->unixTimestamps))
                        $date = Carbon::parse($value);
                else
                        $date = Carbon::parse(Date::excelToDateTimeObject($value));
            }
            else {
                if (isset($this->settings->dateFormats[$columnName])) {
                    $date = Carbon::createFromFormat($this->settings->dateFormats[$columnName], $value);
                }
                else {
                    $date = Carbon::parse($value);
                }
            }

            return $date->format('Y-m-d H:i:s.u');
        }
        return $value;
    }

    private function checkRows($rows) {
        foreach ($rows as $row) {
            $tableRow = [];
            foreach ($row as $column => $value) {
                if ($this->columnExists($column)) {
                    // note: empty values are transformed into their defaults in order to do batch inserts.
                    // laravel doesn't support DEFAULT keyword for insertion
                    $tableRow[$column] = $this->transformEmptyCellValue($column, $value);
                    $tableRow[$column] = $this->transformDateCellValue($column, $tableRow[$column]);
                }
            }
            $this->rows[] = $tableRow;
        }
    }

    public function insertRows($rows) {
        if( empty($rows) ) return;

        $this->checkRows($rows);

        $offset = 0;
        while ($offset < count($this->rows)) {
            $batchRows = array_slice($this->rows, $offset, $this->settings->batchInsertSize);

            DB::table($this->name)->insert($batchRows);

            $offset += $this->settings->batchInsertSize;
        }
        $this->rows = [];
    }

    private function isDateColumn($column) {
        $c = $this->columnInfo[$column];

        // if column is date or time type return
        $dateColumnTypes = ['date', 'date_immutable', 'datetime', 'datetime_immutable', 'datetimez', 'datetimez_immutable', 'time', 'time_immutable', 'dateinterval', 'timestamp'];
        return in_array($c->getType(), $dateColumnTypes);
    }

    public function isNumericColumn($column)
    {
        $c = $this->columnInfo[$column];

        $numericTypes = ['smallint', 'integer', 'bigint', 'tinyint', 'decimal', 'float', 'numeric'];
        if (in_array($c->getType(), $numericTypes)) return true;
        return false;

    }

    public function defaultValue($column) {
        $c = $this->columnInfo[$column];

        // MariaDB returns 'NULL' instead of null like mysql, sqlite, and postgres
        $isNull = is_null($c->getDefault()) || $c->getDefault() === 'NULL';

        // return default value for column if set
        if (! $isNull ) {
            if ($this->isNumericColumn($column)) return intval($c->getDefault());
            return $c->getDefault();
        }

        // if column is auto-incrementing return null and let database set the value
        if ($c->getAutoIncrement()) return null;

        // if column accepts null values, return null
        if ($c->getNullable()) return null;

        // if column is numeric, return 0
        if ($this->isNumericColumn($column)) return 0;

        // if column is date or time type return
        if ($this->isDateColumn($column)) {
            if ($this->settings->timestamps) return date('Y-m-d H:i:s.u');
            else return 0;
        }

        // if column is boolean return false
        if ($c->getType() == "boolean") return false;

        // else return empty string
        return "";
    }
}
