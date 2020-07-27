<?php

namespace bfinlay\SpreadsheetSeeder\Tests;

use bfinlay\SpreadsheetSeeder\SpreadsheetSeederServiceProvider;
use bfinlay\SpreadsheetSeeder\Tests\Seeds\FakeNames100kXlsxSeeder;
use bfinlay\SpreadsheetSeeder\Tests\Seeds\FakeNamesCsvSeeder;
use bfinlay\SpreadsheetSeeder\Tests\Seeds\FakeNamesXlsxSeeder;
use Orchestra\Testbench\TestCase;

class LargeNumberOfRowsTest extends TestCase
{
    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__ . '/migrations');

        // and other test setup steps you need to perform
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }

    protected function getPackageProviders($app)
    {
        return [SpreadsheetSeederServiceProvider::class];
    }

    /** @test */
    public function it_runs_the_migrations()
    {
        $this->assertEquals([
            'id',
            'Gender',
            'Title',
            'GivenName',
            'MiddleInitial',
            'Surname',
            'StreetAddress',
            'City',
            'State',
            'ZipCode',
            'created_at',
            'updated_at'
        ], \Schema::getColumnListing('fake_names'));
    }

    /**
     * @depends it_runs_the_migrations
     *
     * Seed csv file with 15k rows and verify that last entry is accurate
     *
     */
    public function test_15k_rows()
    {
        $this->seed(FakeNamesCsvSeeder::class);

        $fake = \DB::table('fake_names')->where('id', '=', 15000)->first();
        $this->assertEquals('Culver', $fake->Surname);
        $this->assertEquals('Alicia', $fake->GivenName);
    }

    /**
     * @depends it_runs_the_migrations
     *
     * Seed excel file with 15k rows and verify that last entry is accurate
     *
     */
    public function test_15k_xlsx_rows()
    {
        $this->seed(FakeNamesXlsxSeeder::class);

        $fake = \DB::table('fake_names')->where('id', '=', 15000)->first();
        $this->assertEquals('Culver', $fake->Surname);
        $this->assertEquals('Alicia', $fake->GivenName);
        $this->assertEquals(15000, \DB::table('fake_names')->count());
    }

    /**
     * @depends it_runs_the_migrations
     *
     * Seed excel file with 100k rows and verify that last entry is accurate
     *
     * current status Jul 26, 2020:
     * Fails.  See issue #1 https://github.com/bfinlay/laravel-excel-seeder/issues/1
     * `PHP Fatal error:  Allowed memory size of 3221225472 bytes exhausted (tried to allocate 268435464 bytes) in /Users/brion/Sync/laravel-excel-seeder/vendor/phpoffice/phpspreadsheet/src/PhpSpreadsheet/Collection/Cells.php on line 142`
     */
    public function test_100k_xlsx_rows()
    {
        $this->seed(FakeNames100kXlsxSeeder::class);

        $count = \DB::table('fake_names')->count();
        $fake = \DB::table('fake_names')->where('id', '=', 100000)->first();
        $this->assertEquals('Riedel', $fake->Surname);
        $this->assertEquals('Robert', $fake->GivenName);
        $this->assertEquals(100000, \DB::table('fake_names')->count());
    }

}
