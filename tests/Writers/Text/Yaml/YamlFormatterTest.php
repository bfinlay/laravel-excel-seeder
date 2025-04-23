<?php

namespace bfinlay\SpreadsheetSeeder\Tests\Writers\Text\Yaml;

use bfinlay\SpreadsheetSeeder\Writers\Text\Yaml\YamlFormatter;
use Orchestra\Testbench\TestCase;

class YamlFormatterTest extends TestCase
{
    protected static $formatter;

//    public function __construct(?string $name = null, array $data = [], $dataName = '')
//    {
//        parent::__construct($name, $data, $dataName);
//        self::$formatter = new YamlFormatter();
//    }

    protected function setUp(): void
    {
        self::$formatter = self::$formatter ?? new YamlFormatter();
    }

    public function test_header()
    {
        $formatter = self::$formatter;
        $header = $formatter->header(['id','name','description']);
        $this->assertEquals(
"Header:
  - id
  - name
  - description
Rows:
", $header);
    }

    public function test_tableName()
    {
        $formatter = self::$formatter;
        $tableName = $formatter->tableName("products");
        $this->assertEquals("Table: products\n", $tableName);
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
"  1:
    id: 1
    name: hot dog
    description: red german sausage
  2:
    id: 2
    name: cat
    description: small furry mammal
  3:
    id: 3
    name: cactus
    description: green plant with spikes
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
"RowCount: 9
",
        $out
        );
    }
}