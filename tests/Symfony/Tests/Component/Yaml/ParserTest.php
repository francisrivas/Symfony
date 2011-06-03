<?php

/*
 * This file is part of the Symfony package.
 * 
 * (c) Fabien Potencier <fabien@symfony.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Tests\Component\Yaml;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\ParserException;

class ParserTest extends \PHPUnit_Framework_TestCase
{
    protected $parser;
    protected $path;

    static public function setUpBeforeClass()
    {
        Yaml::setSpecVersion('1.1');
    }

    protected function setUp()
    {
        $this->parser = new Parser();
    }

    /**
     * @dataProvider getDataFormSpecifications
     */
    public function testSpecifications($expected, $yaml, $comment)
    {
        $this->assertEquals($expected, var_export($this->parser->parse($yaml), true), $comment);
    }

    public function getDataFormSpecifications()
    {
        $parser = new Parser();
        $path = __DIR__.'/Fixtures';

        $tests = array();
        $files = $parser->parse(file_get_contents($path.'/index.yml'));
        foreach ($files as $file) {
            $yamls = file_get_contents($path.'/'.$file.'.yml');

            // split YAMLs documents
            foreach (preg_split('/^---( %YAML\:1\.0)?/m', $yamls) as $yaml) {
                if (!$yaml) {
                    continue;
                }

                $test = $parser->parse($yaml);
                if (isset($test['todo']) && $test['todo']) {
                    // TODO
                } else {
                    $expected = var_export(eval('return '.trim($test['php']).';'), true);

                    $tests[] = array($expected, $test['yaml'], $test['test']);
                }
            }
        }

        return $tests;
    }

    public function testTabsInYaml()
    {
        // test tabs in YAML
        $yamls = array(
            "foo:\n    bar",
            "foo:\n     bar",
            "foo:\n     bar",
            "foo:\n      bar",
        );

        foreach ($yamls as $yaml) {
            try {
                $content = $this->parser->parse($yaml);

                $this->fail('YAML files must not contain tabs');
            } catch (\Exception $e) {
                $this->assertInstanceOf('\Exception', $e, 'YAML files must not contain tabs');
                $this->assertEquals('A YAML file cannot contain tabs as indentation at line 2 ('.strpbrk($yaml, "\t").').', $e->getMessage(), 'YAML files must not contain tabs');
            }
        }
    }

    public function testEndOfTheDocumentMarker()
    {
        $yaml = <<<EOF
--- %YAML:1.0
foo
...
EOF;

        $this->assertEquals('foo', $this->parser->parse($yaml));
    }

    public function testObjectsSupport()
    {
        $b = array('foo' => new B(), 'bar' => 1);
        $this->assertEquals($this->parser->parse(<<<EOF
foo: !!php/object:O:30:"Symfony\Tests\Component\Yaml\B":1:{s:1:"b";s:3:"foo";}
bar: 1
EOF
        ), $b, '->parse() is able to dump objects');
    }

    public function testNonUtf8Exception()
    {
        if (!function_exists('mb_detect_encoding')) {
            $this->markTestSkipped('Exceptions for non-utf8 charsets require the mb_detect_encoding() function.');

            return;
        }

        $yamls = array(
            iconv("UTF-8", "ISO-8859-1", "foo: 'äöüß'"),
            iconv("UTF-8", "ISO-8859-15", "euro: '€'"),
            iconv("UTF-8", "CP1252", "cp1252: '©ÉÇáñ'")
        );

        foreach ($yamls as $yaml) {
            try {
                $this->parser->parse($yaml);

                $this->fail('charsets other than UTF-8 are rejected.');
            } catch (\Exception $e) {
                 $this->assertInstanceOf('Symfony\Component\Yaml\ParserException', $e, 'charsets other than UTF-8 are rejected.');
            }
        }
    }
}

class B
{
    public $b = 'foo';
}
