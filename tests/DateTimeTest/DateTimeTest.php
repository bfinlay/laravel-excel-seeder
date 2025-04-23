<?php

namespace bfinlay\SpreadsheetSeeder\Tests\DateTimeTest;

use bfinlay\SpreadsheetSeeder\Tests\AssertsMigrations;
use bfinlay\SpreadsheetSeeder\Tests\Seeds\ClassicModelsSeeder;
use bfinlay\SpreadsheetSeeder\Tests\TestCase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\Test;

class DateTimeTest extends TestCase
{
    use AssertsMigrations;

    protected $dateTimeSeeder = DateTimeSeeder::class;

    /** @test */
    #[Test]
    public function it_runs_the_migrations()
    {
        $this->assertsOrdersMigration();
    }

    /**
     * Seed excel spreadsheet and verify that order dates are properly populated
     *
     * Seed classicmodels.xlsx and verify dates in order table
     *
     * @depends it_runs_the_migrations
     */
    #[Depends('it_runs_the_migrations')]
    public function test_order_dates()
    {
        $this->seed(ClassicModelsSeeder::class);

        $order = DB::table('orders')->where('id', 10367)->first();
        $this->assertEquals(205, $order->customer_id);
        $this->assertEquals(326, DB::table('orders')->count());
        $this->assertEquals((new Carbon('January 12 2005')), new Carbon($order->order_date));
        $this->assertEquals((new Carbon('January 21 2005')), new Carbon($order->required_date));
        $this->assertEquals((new Carbon('January 16 2005')), new Carbon($order->shipped_date));
    }

    /**
     * @depends it_runs_the_migrations
     */
    #[Depends('it_runs_the_migrations')]
    public function test_date_formats()
    {
        $startSeedDate = date('Y-m-d H:i:s.u');
        $this->seed($this->dateTimeSeeder);

        $rows = DB::table('date_time_test')->get();
        $fetchRowsDate = date('Y-m-d H:i:s.u');

        $row = $rows[0];
        $this->assertEquals((new Carbon('October 15 2020 23:37')), new Carbon($row->excel_format));
        $this->assertEquals((new Carbon('October 16 2020 04:37:09')), new Carbon($row->unix_format));
        $this->assertEquals((new Carbon('October 04 2020 05:31:02.44')), new Carbon($row->string_format_1));
        $this->assertEquals((new Carbon('October 15 2020')), new Carbon($row->string_format_2));
        $this->assertDateEqualsDefaultValue($row->default_value);
        $this->assertDateBetween($startSeedDate, $fetchRowsDate, $row->created_at);
        $this->assertDateBetween($startSeedDate, $fetchRowsDate, $row->updated_at);

        $columns = ["excel_format", "unix_format", "string_format_1", "string_format_2", "default_value", "created_at", "updated_at"];

        // empty cells
        // explicit null values
        /**
         *
         * empty cells will use the default value if it exists, otherwise will be set to null.
         * The only column with a default value in this test is 'default_value'
         *
         * explicit null cells will be set to null even if a default value exists.
         *
         * input values:
         *
         * excel_format     unix_format     string_format_1     string_format_2     default_value   test
         * (empty)          (empty)         (empty)             (empty)             (empty)         test
         * null             null            null                null                null            test
         *
         * expected values:
         *
         * excel_format     unix_format     string_format_1     string_format_2     default_value       test
         * null             null            null                null                (insertion date)    test
         * null             null            null                null                null                test
         *
         */
        $emptyCellsRow = 1;
        $nullCellsRow = 2;
        $testRows = [$emptyCellsRow, $nullCellsRow];
        foreach ($testRows as $rowIndex) {
            $row = $rows[$rowIndex];
            if ($rowIndex == $emptyCellsRow)
                $columnsWithDates = ['default_value', 'created_at', 'updated_at'];
            else if ($rowIndex == $nullCellsRow)
                $columnsWithDates = ['created_at', 'updated_at'];
            foreach ($columns as $column) {
                if (in_array($column, $columnsWithDates)) {
                    if ($column == 'default_value')
                        $this->assertDateEqualsDefaultValue($row->default_value);
                    else
                        $this->assertDateBetween($startSeedDate, $fetchRowsDate, $row->{$column});
                } else {
                    $this->assertNull($row->{$column});
                }
            }
        }
    }

    public function assertDateEqualsDefaultValue($date)
    {
        $this->assertEquals(
            (new Carbon('2024-08-15 13:49:02'))->roundSeconds(),
            (new Carbon($date))->roundSeconds()
        );
    }

    public function assertDateBetween($start, $end, $date)
    {
        $this->assertGreaterThanOrEqual(new Carbon($start), new Carbon($date));
        $this->assertLessThanOrEqual(new Carbon($end), new Carbon($date));
    }
}
