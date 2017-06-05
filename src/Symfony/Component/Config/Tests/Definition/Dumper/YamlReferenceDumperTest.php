<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Config\Tests\Definition\Dumper;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Dumper\YamlReferenceDumper;
use Symfony\Component\Config\Tests\Fixtures\Configuration\ExampleConfiguration;

class YamlReferenceDumperTest extends TestCase
{
    public function testDumper()
    {
        $configuration = new ExampleConfiguration();

        $dumper = new YamlReferenceDumper();

        $this->assertEquals($this->getConfigurationAsString(), $dumper->dump($configuration));
    }

    public function provideDumpAtPath()
    {
        return array(
            'Regular node' => array('scalar_true', <<<EOL
scalar_true:          true
EOL
            ),
            'Array node' => array('array', <<<EOL
# some info
array:
    child1:               ~
    child2:               ~

    # this is a long
    # multi-line info text
    # which should be indented
    child3:               ~ # Example: example setting
EOL
            ),
            'Regular nested' => array('array.child2', <<<EOL
child2:               ~
EOL
            ),
            'Prototype' => array('cms_pages.page', <<<EOL
# Prototype
page:

    # Prototype
    locale:
        title:                ~ # Required
        path:                 ~ # Required
EOL
            ),
            'Nested prototype' => array('cms_pages.page.locale', <<<EOL
# Prototype
locale:
    title:                ~ # Required
    path:                 ~ # Required
EOL
            ),
        );
    }

    /**
     * @dataProvider provideDumpAtPath
     */
    public function testDumpAtPath($path, $expected)
    {
        $configuration = new ExampleConfiguration();

        $dumper = new YamlReferenceDumper();

        $this->assertSame(trim($expected), trim($dumper->dumpAtPath($configuration, $path)));
    }

    private function getConfigurationAsString()
    {
        return <<<'EOL'
acme_root:
    boolean:              true
    scalar_empty:         ~
    scalar_null:          null
    scalar_true:          true
    scalar_false:         false
    scalar_default:       default
    scalar_array_empty:   []
    scalar_array_defaults:

        # Defaults:
        - elem1
        - elem2
    scalar_required:      ~ # Required
    node_with_a_looong_name: ~
    enum_with_default:    this # One of "this"; "that"
    enum:                 ~ # One of "this"; "that"
    datetime:             ~ # A timestamp or datetime string
    datetime_with_timezone:  ~ # A timestamp or datetime string (default timezone: "Japan")
    datetime_with_format_and_timezone:  ~ # A timestamp or datetime string matching the "d/m/Y" format (default timezone: "Japan")

    # some info
    array:
        child1:               ~
        child2:               ~

        # this is a long
        # multi-line info text
        # which should be indented
        child3:               ~ # Example: example setting
    scalar_prototyped:    []
    parameters:

        # Prototype: Parameter name
        name:                 ~
    connections:

        # Prototype
        -
            user:                 ~
            pass:                 ~
    cms_pages:

        # Prototype
        page:

            # Prototype
            locale:
                title:                ~ # Required
                path:                 ~ # Required
    pipou:

        # Prototype
        name:                 []

EOL;
    }
}
