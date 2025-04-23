<?php

namespace bfinlay\SpreadsheetSeeder\Tests\HashTest;

use bfinlay\SpreadsheetSeeder\Tests\AssertsMigrations;
use bfinlay\SpreadsheetSeeder\Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;

class HashTest extends TestCase
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
        $this->seed(UsersCsvHashSeeder::class);

        $user = DB::table('users')->where('name', 'John')->first();
        $this->assertEquals('John@Doe.com', $user->email);
        $this->assertTrue(Hash::check('password', $user->password));
        $this->assertEquals(2, DB::table('users')->count());
    }
}