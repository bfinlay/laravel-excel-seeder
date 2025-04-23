<?php

namespace bfinlay\SpreadsheetSeeder\Tests\UuidTest;

use bfinlay\SpreadsheetSeeder\Tests\AssertsMigrations;
use bfinlay\SpreadsheetSeeder\Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;

class UuidTest extends TestCase
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
    public function test_uuid_column_added()
    {
        $this->seed(UsersUuidSeeder::class);

        $user = DB::table('users')->where('name', 'John')->first();
        $this->assertNotNull($user->uuid);
        $this->assertTrue(Str::isUuid($user->uuid));
        $this->assertEquals(2, DB::table('users')->count());
    }

    public function test_uuid_column_not_added()
    {
        $this->seed(UsersUuidNoColumnSeeder::class);

        $user = DB::table('users')->where('name', 'John')->first();
        $this->assertNull($user->uuid);
        $this->assertEquals(2, DB::table('users')->count());
    }

}