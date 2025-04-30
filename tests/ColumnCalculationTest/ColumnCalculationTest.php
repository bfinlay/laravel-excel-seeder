<?php

namespace bfinlay\SpreadsheetSeeder\Tests\ColumnCalculationTest;

use AngelSourceLabs\LaravelExpressionGrammar\ExpressionGrammar;
use bfinlay\SpreadsheetSeeder\Tests\AssertsMigrations;
use bfinlay\SpreadsheetSeeder\Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\Test;

/**
 * Test fails with PHPSpreadsheet <= 1.9
 * Passess with PHPSpreadsheet >= 1.10
 */
class ColumnCalculationTest extends TestCase
{
    use AssertsMigrations;

    protected $columnCalculationSeeder = ColumnCalculationSeeder::class;

    /** @test */
    #[Test]
    public function it_runs_the_migrations()
    {
        $this->assertEquals([
            'id',
            'values',
            'function',
            'result',
            'created_at',
            'updated_at',
        ], Schema::getColumnListing('column_calculation_test'));
    }

    /**
     * @depends it_runs_the_migrations
     */
    #[Depends('it_runs_the_migrations')]
    public function test_column_calculations()
    {
        $this->seed($this->columnCalculationSeeder);

        $rows = DB::table('column_calculation_test')->get();

        $row = $rows->firstWhere('function', 'sum');
        $this->assertEquals($row->result, 41858);

        $row = $rows->firstWhere('function', 'average');
        $this->assertEquals($row->result, 4185.8);

        $row = $rows->firstWhere('function', 'count');
        $this->assertEquals($row->result, 10);

        $row = $rows->firstWhere('function', 'max');
        $this->assertEquals($row->result, 8555);

        $row = $rows->firstWhere('function', 'min');
        $this->assertEquals($row->result, 1079);

    }
}
