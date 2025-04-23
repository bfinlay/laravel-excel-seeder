<?php

namespace bfinlay\SpreadsheetSeeder\Tests\ParsersTest;

use bfinlay\SpreadsheetSeeder\Tests\AssertsMigrations;
use bfinlay\SpreadsheetSeeder\Tests\TestCase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;

class ParsersTest extends TestCase
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
        $this->seed(UsersCsvParsersSeeder::class);

        $user = DB::table('users')->where('name', 'John')->first();
        // verify parser has converted email from John@Doe.com to john@doe.com
        $this->assertEquals('john@doe.com', $user->email);
        $this->assertEquals(2, DB::table('users')->count());
    }
}