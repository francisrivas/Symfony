<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Security\Tests\Resources;

class TranslationFilesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provideTranslationFiles
     */
    public function testTranslationFilesAreValid($filePath)
    {
        try {
            \PHPUnit_Util_XML::loadfile($filePath, false, false, true);
        } catch (\PHPUnit_Framework_Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    public function provideTranslationFiles()
    {
        return array_map(
            function ($filePath) { return (array) $filePath; },
            glob(__DIR__.'/../../Resources/translations/*.xlf')
        );
    }
}
