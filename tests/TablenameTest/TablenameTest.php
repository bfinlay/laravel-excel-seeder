<?php

namespace bfinlay\SpreadsheetSeeder\Tests\TablenameTest;

use bfinlay\SpreadsheetSeeder\Tests\AssertsMigrations;
use bfinlay\SpreadsheetSeeder\Tests\Seeds\ClassicModelsSeeder;
use bfinlay\SpreadsheetSeeder\Tests\TestCase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;

class TablenameTest extends TestCase
{
    use AssertsMigrations;
    /** @test */
    #[Test]
    public function it_runs_the_migrations()
    {
        $this->assertsCustomersMigration();
    }

    /**
     * Seed excel spreadsheet and verify that worksheet names match table names
     *
     * Seed classicmodels.xlsx and verify some data in each table
     * Tables are created by the migrations.  In order to test the seed process the test has to verify that data was
     * loaded from a sheet in the spreadsheet to the corresponding table.
     */
    public function test_table_name_is_worksheet_name()
    {
        $this->seed(ClassicModelsSeeder::class);

        $customer = DB::table('customers')->where('id', '=', 103)->first();
        $this->assertEquals('Schmitt', $customer->contact_last_name);
        $this->assertEquals(122, DB::table('customers')->count());

        $employee = DB::table('employees')->where('id', 1216)->first();
        $this->assertEquals('Patterson', $employee->last_name);
        $this->assertEquals(23, DB::table('employees')->count());

        $offices = DB::table('offices')->where('id', 4)->first();
        $this->assertEquals('Paris', $offices->city);
        $this->assertEquals(7, DB::table('offices')->count());

        $orderDetail = DB::table('order_details')->where('id', 470)->first();
        $this->assertEquals('S24_2840', $orderDetail->product_code);
        $this->assertEquals(2996, DB::table('order_details')->count());

        $order = DB::table('orders')->where('id', 10367)->first();
        $this->assertEquals(205, $order->customer_id);
        $this->assertEquals(326, DB::table('orders')->count());

        $payment = DB::table('payments')->where('id', 18)->first();
        $this->assertEquals(101244.59, $payment->amount);
        $this->assertEquals(273, DB::table('payments')->count());

        $product_line = DB::table('product_lines')->where('id', 7)->first();
        $this->assertEquals('Vintage Cars', $product_line->product_line);
        $this->assertEquals(7, DB::table('product_lines')->count());

        $product = DB::table('products')->where('id', 85)->first();
        $this->assertEquals("1980's GM Manhattan Express", $product->name);
        $this->assertEquals(110, DB::table('products')->count());
    }

    /**
     * Seed a CSV file and verify that the CSV filename is used as the table name
     */
    public function test_table_name_is_csv_filename()
    {
        $this->seed(UsersCsvSeeder::class);

        $user = DB::table('users')->where('name', 'John')->first();
        $this->assertEquals('John@Doe.com', $user->email);
        $this->assertEquals(2, DB::table('users')->count());
    }

    /**
     * Seed an XLSX file with only a single named sheet and verify that the worksheet name is used as the table name
     */
    public function test_table_name_is_worksheet_name_for_single_named_sheet()
    {
        $this->seed(OfficesSingleNamedSheetSeeder::class);

        $offices = DB::table('offices')->where('id', 4)->first();
        $this->assertEquals('Paris', $offices->city);
        $this->assertEquals(7, DB::table('offices')->count());
    }

    /**
     * Seed an XLSX file with only a single sheet and verify that the workbook name is used as the table name
     */
    public function test_table_name_is_workbook_name_for_single_unnamed_sheet()
    {
        $this->seed(OfficesSingleUnnamedSheetSeeder::class);

        $offices = DB::table('offices')->where('id', 4)->first();
        $this->assertEquals('Paris', $offices->city);
        $this->assertEquals(7, DB::table('offices')->count());
    }

    /**
     * Seed an XLSX file with only a single sheet and verify that the settings->tablename is used as the table name
     */
    public function test_table_name_is_settings_tablename()
    {
        $this->seed(OfficesSpecifyTablenameSeeder::class);

        $offices = DB::table('offices')->where('id', 4)->first();
        $this->assertEquals('Paris', $offices->city);
        $this->assertEquals(7, DB::table('offices')->count());
    }

    /**
     * Seed an XLSX file with a single sheet that is mapped using settings->worksheetTableMapping
     */
    public function test_table_name_is_single_mapped_named_sheet()
    {
        $this->seed(OfficesSingleMappedNamedSheetSeeder::class);

        $offices = DB::table('offices')->where('id', 4)->first();
        $this->assertEquals('Paris', $offices->city);
        $this->assertEquals(7, DB::table('offices')->count());
    }

    /**
     * Seed an XLSX file with a multiple sheets with two sheets mapped using settings->worksheetTableMapping
     */
    public function test_table_name_is_multiple_mapped_named_sheet()
    {
        $this->seed(ClassicModelsMultipleMappedNamedSheetSeeder::class);

        $employee = DB::table('employees')->where('id', 1216)->first();
        $this->assertEquals('Patterson', $employee->last_name);
        $this->assertEquals('Steve', $employee->first_name);
        $this->assertEquals(23, DB::table('employees')->count());

        $order = DB::table('orders')->where('id', 10407)->first();
        $this->assertEquals(450, $order->customer_id);
        $this->assertEquals('On Hold', $order->status);
        $this->assertEquals(326, DB::table('orders')->count());
    }

}
