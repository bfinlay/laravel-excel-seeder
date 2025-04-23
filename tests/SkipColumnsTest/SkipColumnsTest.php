<?php

namespace bfinlay\SpreadsheetSeeder\Tests\SkipColumnsTest;

use bfinlay\SpreadsheetSeeder\Tests\AssertsMigrations;
use bfinlay\SpreadsheetSeeder\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class SkipColumnsTest extends TestCase
{
    use AssertsMigrations;
    /** @test */
    #[Test]
    public function it_runs_the_migrations()
    {
        $this->assertsCustomersMigration();
    }
}
