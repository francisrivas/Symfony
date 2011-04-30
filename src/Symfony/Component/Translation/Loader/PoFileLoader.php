<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Translation\Loader;

use Symfony\Component\Config\Resource\FileResource;

class PoFileLoader extends ArrayLoader implements LoaderInterface {

    public function load($resource, $locale, $domain = 'messages') {

        $messages = $this->parse($resource);

        // empty file
        if (null === $messages) {
            $messages = array();
        }

        // not an array
        if (!is_array($messages)) {
            throw new \InvalidArgumentException(sprintf('The file "%s" must contain a valid pot file.', $resource));
        }

        $catalogue = parent::load($messages, $locale, $domain);
        $catalogue->addResource(new FileResource($resource));

        return $catalogue;
    }

    /**
     * Parses portable object (PO) format.
     *
     * This parser sacrifices some features of the reference implementation the
     * differences to that implementation are as follows.
     * - No support for comments spanning multiple lines.
     * - Translator and extracted comments are treated as being the same type.
     * - Message IDs are allowed to have other encodings as just US-ASCII.
     *
     * Items with an empty id are ignored. For more information see `merge()`.
     *
     * @param resource $stream
     * @return array
     */
    public function parse($resource) {

        $stream = fopen($resource, 'r');

        $defaults = array(
            'ids' => array(),
            'translated' => null,
            'flags' => array(),
            'comments' => array(),
            'occurrences' => array()
        );

        $translated = array();
        $item = $defaults;

        while ($line = fgets($stream)) {
            $line = trim($line);

            if ($line === '') {
                $item = $defaults;
            } elseif (substr($line, 0, 7) === 'msgid "') {
                $item['ids']['singular'] = substr($line, 7, -1);
            } elseif (substr($line, 0, 8) === 'msgstr "') {
                $item['translated'] = substr($line, 8, -1);
            } elseif ($line[0] === '"') {
                $continues = isset($item['translated']) ? 'translated' : 'ids';

                if (is_array($item[$continues])) {
                    end($item[$continues]);
                    $item[$continues][key($item[$continues])] .= substr($line, 1, -1);
                } else {
                    $item[$continues] .= substr($line, 1, -1);
                }
            } elseif (substr($line, 0, 14) === 'msgid_plural "') {
                $item['ids']['plural'] = substr($line, 14, -1);
            } elseif (substr($line, 0, 7) === 'msgstr[') {
                $item['translated'][(integer) substr($line, 7, 1)] = substr($line, 11, -1);
            }

            foreach ($item['ids'] as $id) {
                $translated[$id] = stripslashes($item['translated']);
            }
        }

        fclose($stream);

		return array_filter($translated);
    }
 }