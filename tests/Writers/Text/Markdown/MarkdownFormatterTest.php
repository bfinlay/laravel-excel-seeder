<?php

namespace bfinlay\SpreadsheetSeeder\Tests\Writers\Text\Markdown;

use bfinlay\SpreadsheetSeeder\Writers\Text\Markdown\MarkdownFormatter;
use Orchestra\Testbench\TestCase;

class MarkdownFormatterTest extends TestCase
{
    protected static $formatter;

//    public function __construct(?string $name = null, array $data = [], $dataName = '')
//    {
//        parent::__construct($name, $data, $dataName);
//        self::$formatter = new MarkdownFormatter();
//    }

    protected function setUp(): void
    {
        self::$formatter = self::$formatter ?? new MarkdownFormatter();
    }

    public function test_header()
    {
        $formatter = self::$formatter;
        $header = $formatter->header(['id','name','description']);
        $this->assertEquals("", $header);
    }

    public function test_tableName()
    {
        $formatter = self::$formatter;
        $tableName = $formatter->tableName("products");
        $this->assertEquals(
"products
========

", $tableName);
    }

    protected function add_rows($i = 1)
    {
        $formatter = self::$formatter;
        $out = $formatter->rows([
            [$i++, "hot dog", "red german sausage"],
            [$i++, "cat", "small furry mammal"],
            [$i++, "cactus", "green plant with spikes"]
        ]);

        return $out;
    }

    /**
     * @depends test_header
     * @return void
     */
    public function test_rows()
    {
        $out = $this->add_rows(1);
        $this->assertEquals(
"| id |  name   |       description       |
|----|---------|-------------------------|
| 1  | hot dog | red german sausage      |
| 2  | cat     | small furry mammal      |
| 3  | cactus  | green plant with spikes |
",
            $out
        );
    }

    /**
     * @depends test_rows
     */
    public function test_footer()
    {
        $formatter = self::$formatter;
        $this->add_rows(4);
        $this->add_rows(7);
        $out = $formatter->footer();

        $this->assertEquals(
"(9 rows)

",
        $out
        );
    }
}