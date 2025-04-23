<?php

namespace bfinlay\SpreadsheetSeeder\Tests\LargeNumberOfRowsTest;

use bfinlay\SpreadsheetSeeder\Tests\AssertsMigrations;
use bfinlay\SpreadsheetSeeder\Tests\TestCase;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\Test;

class LargeNumberOfRowsTest extends TestCase
{
    use AssertsMigrations;

    /** @test */
    #[Test]
    public function it_runs_the_migrations()
    {
        $this->assertsFakeNamesMigration();
    }

    public function should_run_large_rows_tests()
    {
        $testName = (method_exists($this, "getName")) ? $this->getName() : $this->name();

        if (env('LARGE_ROWS_TESTS', false) == false)
            $this->markTestSkipped('Skipping ' . $testName . ' because LARGE_ROWS_TESTS is false.  Enable LARGE_ROWS_TESTS in phpunit.xml to run test');
    }

    /**
     * @depends it_runs_the_migrations
     *
     * Seed csv file with 15k rows and verify that last entry is accurate
     *
     */
    #[Depends('it_runs_the_migrations')]
    public function test_15k_rows()
    {
        $this->should_run_large_rows_tests();

        $this->seed(FakeNamesCsvSeeder::class);

        $fake = DB::table('fake_names')->where('id', '=', 15000)->first();
        $this->assertEquals('Culver', $fake->Surname);
        $this->assertEquals('Alicia', $fake->GivenName);
    }

    /**
     * @depends it_runs_the_migrations
     *
     * Seed excel file with 15k rows and verify that last entry is accurate
     *
     */
    #[Depends('it_runs_the_migrations')]
    public function test_15k_xlsx_rows()
    {
        $this->should_run_large_rows_tests();

        $this->seed(FakeNamesXlsxSeeder::class);

        $fake = DB::table('fake_names')->where('id', '=', 15000)->first();
        $this->assertEquals('Culver', $fake->Surname);
        $this->assertEquals('Alicia', $fake->GivenName);
        $this->assertEquals(15000, DB::table('fake_names')->count());
    }

    /**
     * @depends it_runs_the_migrations
     *
     * Seed excel file with 100k rows and verify that last entry is accurate
     *
     * current status Jul 28, 2020:
     * test passes
     * disabled by default because it takes 30 min to run.  remove "disabled_" to run.
     */
    #[Depends('it_runs_the_migrations')]
    public function disabled_test_100k_xlsx_rows()
    {
        $this->should_run_large_rows_tests();

        $this->seed(FakeNames100kXlsxSeeder::class);

        $count = DB::table('fake_names')->count();
        $fake = DB::table('fake_names')->where('id', '=', 100000)->first();
        $this->assertEquals('Riedel', $fake->Surname);
        $this->assertEquals('Robert', $fake->GivenName);
        $this->assertEquals(100000, DB::table('fake_names')->count());
    }

}
