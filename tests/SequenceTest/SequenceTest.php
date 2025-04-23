<?php

namespace bfinlay\SpreadsheetSeeder\Tests\SequenceTest;

use bfinlay\SpreadsheetSeeder\Tests\ParsersTest\UsersCsvParsersSeeder;
use bfinlay\SpreadsheetSeeder\Tests\TestCase;
use bfinlay\SpreadsheetSeeder\Writers\Database\DatabaseWriter;
use Illuminate\Database\Query\Grammars\PostgresGrammar;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SequenceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (!DB::connection()->getQueryGrammar() instanceof PostgresGrammar) {
            $this->markTestSkipped("Test skipped for " . get_class(DB::connection()->getQueryGrammar()) . ".  Test is for Postgres only.");
//            return;
        }

        $this->loadMigrationsFrom(__DIR__);

        // and other test setup steps you need to perform
    }

    public function assert_users_table_seeded_correctly($table = 'users', $primaryKey = 'id')
    {
        $user = DB::table($table)->where('name', 'John')->first();
        // verify parser has converted email from John@Doe.com to john@doe.com
        $this->assertEquals('john@doe.com', $user->email);
        $this->assertEquals(2, DB::table($table)->count());

        // Foo,Foo@Bar.com,2019-01-23 21:38:54,password
        DB::table($table)->insert([
                'name' => 'Francis',
                'email' => 'FrancisNMartin@einrot.com',
                'email_verified_at' => '2023-01-21 11:18:00',
                'password' => 'password'
            ]
        );
        $user = DB::table($table)->where('name', 'Francis')->first();
        $this->assertEquals(6, $user->$primaryKey);
    }

    /** @test */
    #[Test]
    public function it_runs_the_migrations()
    {
        $tables = ['users0', 'users_seq1', 'users2', 'users2_account', 'users3'];
        $primary = ['id', 'users_seq1_id', 'account_id', 'id', 'id'];
        $order = ['users3'];
        foreach($tables as $key => $table) {
            $columns = [
                $primary[$key],
                'name',
                'email',
                'email_verified_at',
                'password',
                'created_at',
                'updated_at',
            ];
            if (in_array($table, $order)) {
                $columns[] = 'order';
            }
            $schema = Schema::getColumnListing($table);
            $this->assertEquals(
                $columns,
                Schema::getColumnListing($table),
                "table = $table"
            );
        }
    }

    /**
     * Seed a CSV file and verify that the CSV filename is used as the table name
     *
     * Seed file and verify sequence counter is incremented to 2
     * Add user to table and verify id number is 3
     */
    public function test_sequence_counter_is_updated()
    {
        $this->seed(UsersCsvParsersSeeder::class);
        $this->assert_users_table_seeded_correctly();
    }

    public function test_table_name_in_seq_column_name()
    {
        $tableSequences = DatabaseWriter::getSequencesForTable('users_seq1');
        $this->seed(UsersSeq1Seeder::class);
        $this->assert_users_table_seeded_correctly('users_seq1', 'users_seq1_id');
    }

    // test fail when sequence doesn't update
    public function test_insert_fails_if_sequence_counter_is_not_updated()
    {
        app()->bind(DatabaseWriter::class, \bfinlay\SpreadsheetSeeder\Tests\SequenceTest\DatabaseWriter::class);
        $this->seed(UsersCsvParsersSeeder::class);

        $this->expectExceptionMessage("Unique violation: 7 ERROR:  duplicate key value violates unique constraint");
        $this->assert_users_table_seeded_correctly();
    }

    /**
     *
     * scenario where table and column names overlap
     * users2, account_id --> users2_account_id_seq
     * users2_account, id --> users2_account_id_seq
     *
     * @return void
     */
    public function test_table_and_column_name_conflict()
    {
        $this->seed(Users2Seeder::class);
        $this->seed(Users2AccountSeeder::class);
        $sequence['users2'] = DatabaseWriter::getSequencesForTable('users2');
        $sequence['users2_account'] = DatabaseWriter::getSequencesForTable('users2_account');
        $this->assert_users_table_seeded_correctly('users2', 'account_id');
        $this->assert_users_table_seeded_correctly('users2_account', 'id');
    }

    // test two sequences
    public function test_table_with_two_sequences()
    {
        $sequences = DatabaseWriter::getSequencesForTable('users3');
        $this->seed(Users3Seeder::class);
        $this->assert_users_table_seeded_correctly('users3');
    }
 }