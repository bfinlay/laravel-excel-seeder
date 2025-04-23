<?php

namespace bfinlay\SpreadsheetSeeder\Tests\SelectiveWorksheetTest;

use bfinlay\SpreadsheetSeeder\SpreadsheetSeederSettings;
use bfinlay\SpreadsheetSeeder\Tests\AssertsMigrations;
use bfinlay\SpreadsheetSeeder\Tests\Seeds\ClassicModelsSeeder;
use bfinlay\SpreadsheetSeeder\Tests\TestCase;
use bfinlay\SpreadsheetSeeder\Writers\Database\DestinationTable;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;

class SelectiveWorksheetTest extends TestCase
{
    use AssertsMigrations;

    public function setUp(): void
    {
        parent::setUp();

        $tables = [
            'customers',
            'employees',
            'offices',
            'order_details',
            'orders',
            'payments',
            'product_lines',
            'products',
        ];
        foreach ($tables as $table)
            new DestinationTable($table); // truncates table if it exists
    }


    /** @test */
    #[Test]
    public function it_runs_the_migrations()
    {
        $this->assertsCustomersMigration();
    }

    /**
     * @return SpreadsheetSeederSettings
     */
    protected function settings()
    {
        return resolve(SpreadsheetSeederSettings::class);
    }

    public function test_all_tables_seeded_when_settings_worksheets_is_empty_array()
    {
        $this->settings()->worksheets = [];

        $this->seed(ClassicModelsSeeder::class);

        $this->assertTableHasExpectedData([
            'customers',
            'employees',
            'offices',
            'order_details',
            'orders',
            'payments',
            'product_lines',
            'products',
        ]);
    }

    public function test_all_tables_seeded_when_settings_skipsheets_is_empty_array()
    {
        $this->settings()->skipSheets = [];

        $this->seed(ClassicModelsSeeder::class);

        $this->assertTableHasExpectedData([
            'customers',
            'employees',
            'offices',
            'order_details',
            'orders',
            'payments',
            'product_lines',
            'products',
        ]);
    }

    public function test_all_tables_seeded_when_settings_worksheets_is_null()
    {
        $this->settings()->worksheets = null;

        $this->seed(ClassicModelsSeeder::class);

        $this->assertTableHasExpectedData([
            'customers',
            'employees',
            'offices',
            'order_details',
            'orders',
            'payments',
            'product_lines',
            'products',
        ]);
    }

    public function test_all_tables_seeded_when_settings_skipsheets_is_null()
    {
        $this->settings()->skipSheets = null;

        $this->seed(ClassicModelsSeeder::class);

        $this->assertTableHasExpectedData([
            'customers',
            'employees',
            'offices',
            'order_details',
            'orders',
            'payments',
            'product_lines',
            'products',
        ]);
    }

    public function test_all_tables_seeded_when_settings_worksheets_is_nonarray_string()
    {
        $this->settings()->worksheets = "not_an_array";

        $this->seed(ClassicModelsSeeder::class);

        $this->assertTableHasExpectedData([
            'customers',
            'employees',
            'offices',
            'order_details',
            'orders',
            'payments',
            'product_lines',
            'products',
        ]);
    }

    public function test_all_tables_seeded_when_settings_skipsheets_is_nonarray_string()
    {
        $this->settings()->skipSheets = "not_an_array";

        $this->seed(ClassicModelsSeeder::class);

        $this->assertTableHasExpectedData([
            'customers',
            'employees',
            'offices',
            'order_details',
            'orders',
            'payments',
            'product_lines',
            'products',
        ]);
    }

    public function test_all_tables_seeded_when_settings_worksheets_is_nonarray_int()
    {
        $this->settings()->worksheets = 42;

        $this->seed(ClassicModelsSeeder::class);

        $this->assertTableHasExpectedData([
            'customers',
            'employees',
            'offices',
            'order_details',
            'orders',
            'payments',
            'product_lines',
            'products',
        ]);
    }

    public function test_all_tables_seeded_when_settings_skipsheets_is_nonarray_int()
    {
        $this->settings()->skipSheets = 42;

        $this->seed(ClassicModelsSeeder::class);

        $this->assertTableHasExpectedData([
            'customers',
            'employees',
            'offices',
            'order_details',
            'orders',
            'payments',
            'product_lines',
            'products',
        ]);
    }

    public function test_only_specified_worksheets_have_data()
    {
        $this->settings()->worksheets = ['offices', 'employees'];

        $this->seed(ClassicModelsSeeder::class);

        $this->assertTableHasExpectedData([
            'offices',
            'employees',
        ]);

        $emptyTables = [
            'customers',
            'order_details',
            'orders',
            'payments',
            'product_lines',
            'products',
        ];

        foreach ($emptyTables as $table) $this->assertEquals(0, DB::table($table)->count());
    }

    public function test_only_specified_skipsheets_have_data()
    {
        $this->settings()->skipSheets = [
            'customers',
            'order_details',
            'orders',
            'payments',
            'product_lines',
            'products',
        ];

        $this->seed(ClassicModelsSeeder::class);

        $this->assertTableHasExpectedData([
            'offices',
            'employees',
        ]);

        $emptyTables = [
            'customers',
            'order_details',
            'orders',
            'payments',
            'product_lines',
            'products',
        ];

        foreach ($emptyTables as $table) $this->assertEquals(0, DB::table($table)->count());
    }

    public function test_all_tables_empty_when_nonexistant_table_specified()
    {
        $this->settings()->worksheets = ['nonexistant'];


        $emptyTables = [
            'offices',
            'employees',
            'customers',
            'order_details',
            'orders',
            'payments',
            'product_lines',
            'products',
        ];

        foreach ($emptyTables as $table) $this->assertEquals(0, DB::table($table)->count(), "pre-seed.  table = $table");

        $this->seed(ClassicModelsSeeder::class);

        foreach ($emptyTables as $table) $this->assertEquals(0, DB::table($table)->count(), "table = $table");
    }

    public function test_xl_seed_command_no_sheet_option()
    {
        $testTables = [
            'offices',
            'employees',
            'customers',
            'order_details',
            'orders',
            'payments',
            'product_lines',
            'products',
        ];

        $class = str_replace('\\', '\\\\', ClassicModelsSeeder::class);
        $this->artisan("xl:seed --class=$class");

        $this->assertTableHasExpectedData($testTables);
    }

    public function test_xl_seed_command_single_sheet_option()
    {
        $testTables = [
            'offices',
        ];

        $emptyTables = [
            'employees',
            'customers',
            'order_details',
            'orders',
            'payments',
            'product_lines',
            'products',
        ];

        $class = str_replace('\\', '\\\\', ClassicModelsSeeder::class);
        $this->artisan("xl:seed --class=$class --sheet=offices");

//        $this->artisan('xl:seed', ['--class' => ClassicModelsSeeder::class, '--sheet' => 'offices']);

        $this->assertTableHasExpectedData($testTables);

        foreach ($emptyTables as $table) $this->assertEquals(0, DB::table($table)->count());
    }

    public function test_xl_seed_command_multiple_sheet_options()
    {
        $testTables = [
            'offices',
            'employees',
        ];

        $emptyTables = [
            'customers',
            'order_details',
            'orders',
            'payments',
            'product_lines',
            'products',
        ];

        $class = str_replace('\\', '\\\\', ClassicModelsSeeder::class);
        $this->artisan("xl:seed --class=$class --sheet=offices --sheet=employees");

//        $this->artisan('xl:seed', ['--class' => ClassicModelsSeeder::class, '--sheet' => ['offices', 'employees']]);

        $this->assertTableHasExpectedData($testTables);

        foreach ($emptyTables as $table) $this->assertEquals(0, DB::table($table)->count());
    }

    public function test_xl_seed_command_single_sheet_argument()
    {
        $testTables = [
            'offices',
        ];

        $emptyTables = [
            'employees',
            'customers',
            'order_details',
            'orders',
            'payments',
            'product_lines',
            'products',
        ];

        $class = str_replace('\\', '\\\\', ClassicModelsSeeder::class);
        $this->artisan("xl:seed $class offices");

        $this->assertTableHasExpectedData($testTables);

        foreach ($emptyTables as $table) $this->assertEquals(0, DB::table($table)->count());
    }

    public function test_xl_seed_command_multiple_sheet_arguments()
    {
        $testTables = [
            'offices',
            'employees',
        ];

        $emptyTables = [
            'customers',
            'order_details',
            'orders',
            'payments',
            'product_lines',
            'products',
        ];

        $class = str_replace('\\', '\\\\', ClassicModelsSeeder::class);
        $this->artisan("xl:seed $class offices employees");

        $this->assertTableHasExpectedData($testTables);

        foreach ($emptyTables as $table) $this->assertEquals(0, DB::table($table)->count());
    }

    public function test_xl_seed_command_class_hash_option()
    {
        $testTables = [
            'offices',
            'employees',
        ];

        $emptyTables = [
            'customers',
            'order_details',
            'orders',
            'payments',
            'product_lines',
            'products',
        ];

        foreach (array_merge($testTables, $emptyTables) as $table) $this->assertEquals(0, DB::table($table)->count());

        $this->settings()->file = '/../../../../examples/classicmodels.xlsx';
        $this->artisan("xl:seed --class=# --sheet=offices --sheet=employees");

        $this->assertTableHasExpectedData($testTables);

        foreach ($emptyTables as $table) $this->assertEquals(0, DB::table($table)->count());
    }

    public function test_xl_seed_command_class_hash_argument()
    {
        $testTables = [
            'offices',
            'employees',
        ];

        $emptyTables = [
            'customers',
            'order_details',
            'orders',
            'payments',
            'product_lines',
            'products',
        ];

        foreach (array_merge($testTables, $emptyTables) as $table) $this->assertEquals(0, DB::table($table)->count());

        $this->settings()->file = '/../../../../examples/classicmodels.xlsx';
        $this->artisan("xl:seed # offices employees");

        $this->assertTableHasExpectedData($testTables);

        foreach ($emptyTables as $table) $this->assertEquals(0, DB::table($table)->count());
    }

    public function test_xl_seed_command_class_spreadsheet_seeder_argument()
    {
        $testTables = [
            'offices',
            'employees',
        ];

        $emptyTables = [
            'customers',
            'order_details',
            'orders',
            'payments',
            'product_lines',
            'products',
        ];

        foreach (array_merge($testTables, $emptyTables) as $table) $this->assertEquals(0, DB::table($table)->count());

        $this->settings()->file = '/../../../../examples/classicmodels.xlsx';
        $this->artisan("xl:seed SpreadsheetSeeder offices employees");

        $this->assertTableHasExpectedData($testTables);

        foreach ($emptyTables as $table) $this->assertEquals(0, DB::table($table)->count());
    }

    /**
     * @param $table string|array
     */
    public function assertTableHasExpectedData($table)
    {
        if (is_string($table)) $table = [$table];

        if (in_array('customers', $table)) {
            $customer = DB::table('customers')->where('id', '=', 103)->first();
            $this->assertEquals('Schmitt', $customer->contact_last_name);
            $this->assertEquals(122, DB::table('customers')->count());
        }

        if (in_array('employees', $table)) {
            $employee = DB::table('employees')->where('id', 1216)->first();
            $this->assertEquals('Steve', $employee->first_name);
            $this->assertEquals('Patterson', $employee->last_name);
            $this->assertEquals(23, DB::table('employees')->count());
        }

        if (in_array('offices', $table)) {
            $offices = DB::table('offices')->where('id', 4)->first();
            $this->assertEquals('Paris', $offices->city);
            $this->assertEquals(7, DB::table('offices')->count());
        }

        if (in_array('order_details', $table)) {
            $orderDetail = DB::table('order_details')->where('id', 470)->first();
            $this->assertEquals('S24_2840', $orderDetail->product_code);
            $this->assertEquals(2996, DB::table('order_details')->count());
        }

        if (in_array('orders', $table)) {
            $order = DB::table('orders')->where('id', 10407)->first();
            $this->assertEquals(450, $order->customer_id);
            $this->assertEquals('On Hold', $order->status);
            $this->assertEquals(326, DB::table('orders')->count());
        }

        if (in_array('payments', $table)) {
            $payment = DB::table('payments')->where('id', 18)->first();
            $this->assertEquals(101244.59, $payment->amount);
            $this->assertEquals(273, DB::table('payments')->count());
        }

        if (in_array('product_lines', $table)) {
            $product_line = DB::table('product_lines')->where('id', 7)->first();
            $this->assertEquals('Vintage Cars', $product_line->product_line);
            $this->assertEquals(7, DB::table('product_lines')->count());
        }

        if (in_array('products', $table)) {
            $product = DB::table('products')->where('id', 85)->first();
            $this->assertEquals("1980's GM Manhattan Express", $product->name);
            $this->assertEquals(110, DB::table('products')->count());
        }
    }
}
