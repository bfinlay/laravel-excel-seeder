<?php

namespace bfinlay\SpreadsheetSeeder\Tests\Writers\Text;

use bfinlay\SpreadsheetSeeder\SpreadsheetSeederServiceProvider;
use bfinlay\SpreadsheetSeeder\SpreadsheetSeederSettings;
use bfinlay\SpreadsheetSeeder\Tests\Seeds\ClassicModelsSeeder;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Finder\Finder;

/**
 * Class TextOutputTableTest
 * @package bfinlay\SpreadsheetSeeder\Tests
 *
 * 1. test that all files are created
 * 2. verify that all files are deleted before creating
 * 3. verify that table that has been removed from source workbook is not recreated
 */
class TextOutputTableTest extends TestCase
{
    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__ . '/../../migrations');

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
    #[Test]
    public function it_runs_the_migrations()
    {
        $this->assertEquals([
            'id',
            'order_date',
            'required_date',
            'shipped_date',
            'status',
            'comments',
            'customer_id',
            'created_at',
            'updated_at'
        ], Schema::getColumnListing('orders'));
    }

    /**
     * @depends it_runs_the_migrations
     */
    #[Depends('it_runs_the_migrations')]
    public function test_seeder_text_output_tables()
    {
        $path = __DIR__ . '/../../../examples/classicmodels';

        $deletedSheetName = "deleted_sheet_test.md";

        $deletedSheet = new \SplFileObject($path . "/" . $deletedSheetName, "w");
        $deletedSheet->fwrite("Test that this file is deleted by the seeder.");
        $deletedSheet->fflush();
        $deletedSheet = null; // close file handle

        $this->assertFileExists($path . "/" . $deletedSheetName);

        /**
         * @var SpreadsheetSeederSettings $settings
         */
        $settings = resolve(SpreadsheetSeederSettings::class);
        $settings->textOutput = ['yaml', 'markdown'];

        $this->seed(ClassicModelsSeeder::class);
        
        $expectedFiles = [
            'customers.md' => false,
            'employees.md' => false,
            'offices.md' => false,
            'order_details.md' => false,
            'orders.md' => false,
            'payments.md' => false,
            'product_lines.md' => false,
            'products.md' => false,
            'customers.yaml' => false,
            'employees.yaml' => false,
            'offices.yaml' => false,
            'order_details.yaml' => false,
            'orders.yaml' => false,
            'payments.yaml' => false,
            'product_lines.yaml' => false,
            'products.yaml' => false,
        ];

        $finder = new Finder();
        $finder->in($path)->files();
        
        foreach ($finder as $file) {
            $this->assertTrue(isset($expectedFiles[$file->getFilename()]), "Unexpected file " . $file->getFilename() . ".");
            $this->assertFalse($expectedFiles[$file->getFilename()], "File " . $file->getFilename() . " has been previously tested which implies multiple copies and should not occur.");
            $expectedFiles[$file->getFilename()] = true;
        }
        
        foreach ($expectedFiles as $file => $tested) {
            $this->assertTrue($tested, "Expected file $file was not created.");
        }
    }
}
