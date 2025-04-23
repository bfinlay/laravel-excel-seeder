<?php

namespace bfinlay\SpreadsheetSeeder\Tests\SettingsTest;

use bfinlay\SpreadsheetSeeder\Tests\AssertsMigrations;
use bfinlay\SpreadsheetSeeder\Tests\TestCase;
use bfinlay\SpreadsheetSeeder\Tests\UuidTest\UsersUuidSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;

class SettingsTest extends TestCase
{
    use AssertsMigrations;
    /** @test */
    #[Test]
    public function it_runs_the_migrations()
    {
        $this->assertsUsersMigration();
    }

    /**
     * Seed a CSV file and verify that the CSV filename is used as the table name
     */
    public function test_table_name_is_csv_filename()
    {
        $this->seed(UsersUuidSeeder::class);

        $user = DB::table('users')->where('name', 'John')->first();
        $this->assertNotNull($user->uuid);
        $this->assertTrue(Str::isUuid($user->uuid));
        $this->assertEquals(2, DB::table('users')->count());
    }

    public function test_sheetnames()
    {
        $this->seed(ClassicModelsSheetNameTestSeeder::class);

        $employees = [
            6002 => ["last" => "Murphy", "first" => "Diane"],
            6056 => ["last" => "Patterson", "first" => "Mary"],
            6076 => ["last" => "Firrelli", "first" => "Jeff"],
            6088 => ["last" => "Patterson", "first" => "William"],
            6102 => ["last" => "Bondur", "first" => "Gerard"],
            6143 => ["last" => "Bow", "first" => "Anthony"],
            6165 => ["last" => "Jennings", "first" => "Leslie"],
            6166 => ["last" => "Thompson", "first" => "Leslie"],
            6188 => ["last" => "Firrelli", "first" => "Julie"],
            6216 => ["last" => "Patterson", "first" => "Steve"],
            6286 => ["last" => "Tseng", "first" => "Foon Yue"],
            6323 => ["last" => "Vanauf", "first" => "George"],
            6337 => ["last" => "Bondur", "first" => "Loui"],
            6370 => ["last" => "Hernandez", "first" => "Gerard"],
            6401 => ["last" => "Castillo", "first" => "Pamela"],
            6501 => ["last" => "Bott", "first" => "Larry"],
            6504 => ["last" => "Jones", "first" => "Barry"],
            6611 => ["last" => "Fixter", "first" => "Andy"],
            6612 => ["last" => "Marsh", "first" => "Peter"],
            6619 => ["last" => "King", "first" => "Tom"],
            6621 => ["last" => "Nishi", "first" => "Mami"],
            6625 => ["last" => "Kato", "first" => "Yoshimi"],
            6702 => ["last" => "Gerard", "first" => "Martin"],
        ];

        foreach ($employees as $id => $name) {
            $employee = DB::table('employees')
                ->where('first_name', $name["first"])
                ->where('last_name', $name["last"])
                ->first();
            $this->assertEquals($id, $employee->id);
        }
        $this->assertEquals(23, DB::table('employees')->count());
    }

    public function test_filename()
    {
        $this->seed(ClassicModelsFileNameTestSeeder::class);

        $users = [
            5001 => "Foo",
            5005 => "John"
        ];

        foreach ($users as $id => $name) {
            $user = DB::table('users')
                ->where('name', $name)
                ->first();
            $this->assertEquals($id, $user->id);
        }
        $this->assertEquals(2, DB::table('users')->count());

    }
}