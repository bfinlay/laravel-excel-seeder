<?php


namespace bfinlay\SpreadsheetSeeder\Tests\ColumnCalculationTest;

use bfinlay\SpreadsheetSeeder\SpreadsheetSeeder;
use bfinlay\SpreadsheetSeeder\SpreadsheetSeederSettings;
use bfinlay\SpreadsheetSeeder\Tests\TestsPath;

class ColumnCalculationSeeder extends SpreadsheetSeeder
{
    public function settings(SpreadsheetSeederSettings $set)
    {
        $set->file = TestsPath::forSettings('ColumnCalculationTest/ColumnCalculationTest.xlsx');
        $set->textOutput = false;
    }
}