<?php

namespace bfinlay\SpreadsheetSeeder\Tests\EmptyValueTest;

use AngelSourceLabs\LaravelExpressionGrammar\ExpressionGrammar;
use bfinlay\SpreadsheetSeeder\Tests\AssertsMigrations;
use bfinlay\SpreadsheetSeeder\Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\Test;

class EmptyValueTest extends TestCase
{
    use AssertsMigrations;

    protected $emptyValueSeeder = EmptyValueSeeder::class;

    /** @test */
    #[Test]
    public function it_runs_the_migrations()
    {
        $this->assertEquals([
            'id',
            'null_column',
            'true_column',
            'false_column',
            'skip_row',
            'blank_column',
            'created_at',
            'updated_at',
        ], Schema::getColumnListing('empty_value_test'));
    }

    public function trueGrammar()
    {
        return ExpressionGrammar::make()
            ->sqLite(1)
            ->mySql(1)
            ->postgres(1);
    }

    public function falseGrammar()
    {
        return ExpressionGrammar::make()
            ->sqLite(0)
            ->mySql(0)
            ->postgres(0);
    }

    public function assertFieldIsTrue($actual)
    {
        $this->assertEquals($this->trueGrammar()->resolve(), $actual);
    }

    public function assertFieldIsFalse($actual)
    {
        $this->assertEquals($this->falseGrammar()->resolve(), $actual);
    }

    /**
     * @depends it_runs_the_migrations
     */
    #[Depends('it_runs_the_migrations')]
    public function test_empty_values()
    {
        $this->seed($this->emptyValueSeeder);

        $rows = DB::table('empty_value_test')->get();

        /**
         * #### Empty value rules
         * 1. String conversions: 'null' is converted to `NULL`, 'true' is converted to `TRUE`, 'false' is converted to `FALSE`
         * 2. 'null' strings converted to `NULL` are treated as explicit nulls.  They are not subject to implicit conversions to default values.
         * 3. Empty cells are set to the default value specified in the database table data definition, unless the entire row is empty
         * 4. Cells with empty strings (ie `""`) are treated as an empty cell.  This can be changed by setting `emptyStringIsEmptyCell` to `FALSE`.
         * 5. If the entire row consists of empty cells, the row is skipped.  To intentionally insert a null row, put the string value 'null' in each cell
         */

        /**
         * todo
         * how should empty strings be treated?  currently treated as an empty string and not converted to default value.
         * but in excel, this is the closest you can get to a null value as the output of an if macro
         * setting: emptyStringIsEmptyCell
         */

        /**
         * Test string conversions
         * This tests rule 1 and rule 2
         */
        $row = $rows->firstWhere('id', 1);
        $this->assertNull($row->null_column);
        $this->assertFieldIsTrue($row->true_column);
        $this->assertFieldIsFalse($row->false_column);
        $this->assertFieldIsFalse($row->skip_row);

        /**
         * Test Excel types
         *
         * null_column: the value in the null_column is supposedly an "Excel Null" value created by entering a value and
         * then using find/replace with an empty replace as suggested at this link:
         * https://stackoverflow.com/questions/2558216/output-a-null-cell-value-in-excel
         * This software will interpret that as an empty value and follow rule 3: empty cells are set to the default value specified in the database table.
         *
         * true_column: set to excel TRUE type.
         * false_column: set to excel FALSE type.
         * skip_row: set to excel FALSE type.
         */
        $row = $rows->firstWhere('id', 2);
        $this->assertEquals("default value", Str::trim($row->null_column));
        $this->assertFieldIsTrue($row->true_column);
        $this->assertFieldIsFalse($row->false_column);
        $this->assertFieldIsFalse($row->skip_row);

        /**
         * Test empty strings
         *
         * The columns use the formula `=IF(1=0,"","")` to set an empty string.
         * All columns should use default column value
         *
         * null_column: "default value"
         * true_column: FALSE
         * false_column: TRUE
         * skip_row: is set to false to prevent ignoring entire row per rule 4
         */
        $row = $rows->firstWhere('id', 3);
        $this->assertEquals("default value", Str::trim($row->null_column));
        $this->assertFieldIsFalse($row->true_column);
        $this->assertFieldIsTrue($row->false_column);
        $this->assertFieldIsFalse($row->skip_row);

        /**
         * Test blank cells
         *
         * The columns are all blank cells.
         * All columns should use default column value
         *
         * null_column: "default value"
         * true_column: FALSE
         * false_column: TRUE
         * skip_row: is set to false to prevent ignoring entire row per rule 4
         */
        $row = $rows->firstWhere('id', 4);
        $this->assertEquals("default value", Str::trim($row->null_column));
        $this->assertFieldIsFalse($row->true_column);
        $this->assertFieldIsTrue($row->false_column);
        $this->assertFieldIsFalse($row->skip_row);

        /**
         * test rule 4, skip empty rows using blank cells or empty strings
         * In the first row (what would be id=5), the columns are all blank cells.
         * In the second row (what would be id=6), the columns use the formula `=IF(1=0,"","")` to set an empty string.
         *
         * both rows should be skipped, so that the total number of rows in the table is 5 instead of 7
         */
        $this->assertCount(6, $rows);

        /**
         *  Test Explicit "null" values
         *  test rules 1 and 5, explicit "null" is inserted as NULL and not default values
         *
         *  The columns are all "null" (except "blank_column" is blank).
         *  All columns should be NULL and not default values
         *
         *  null_column: NULL
         *  true_column: NULL
         *  false_column: NULL
         *  skip_row: NULL
 */
        $row = $rows->firstWhere('id', 7);
        $this->assertNull($row->null_column);
        $this->assertNull($row->true_column);
        $this->assertNull($row->false_column);
        $this->assertNull($row->skip_row);
    }
}
