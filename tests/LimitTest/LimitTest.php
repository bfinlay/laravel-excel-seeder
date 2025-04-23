<?php

namespace bfinlay\SpreadsheetSeeder\Tests\LimitTest;

use bfinlay\SpreadsheetSeeder\Tests\AssertsMigrations;
use bfinlay\SpreadsheetSeeder\Tests\LargeNumberOfRowsTest\LargeNumberOfRowsTest;
use bfinlay\SpreadsheetSeeder\Tests\TestCase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\DependsExternal;
use PHPUnit\Framework\Attributes\Test;

class LimitTest extends TestCase
{
    use AssertsMigrations;

    /** @test */
    #[Test]
    public function it_runs_the_migrations()
    {
        $this->assertsFakeNamesMigration();
    }

    /**
     * @depends it_runs_the_migrations
     * @Depends LargeNumberOfRowsTest::test_15k_xlsx_rows
     */
    #[Depends('it_runs_the_migrations')]
    #[DependsExternal('bfinlay\SpreadsheetSeeder\Tests\LargeNumberOfRowsTest\LargeNumberOfRowsTest', 'test_15k_xlsx_rows')]
    public function test_limit()
    {
        // limit seeder sets $limit setting to 5000 to seed only the first 5000 rows
        $this->seed(LimitSeeder::class);

        // check that exactly 5000 rows are seeded and no more
        $this->assertEquals(5000, DB::table('fake_names')->count());

        // check row 5000 is seeded
        $fake = DB::table('fake_names')->where('id', '=', 5000)->first();
        $this->assertEquals('Venuti', $fake->Surname);
        $this->assertEquals('Samuel', $fake->GivenName);
    }
}
