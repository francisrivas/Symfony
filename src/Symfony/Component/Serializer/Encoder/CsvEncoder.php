<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Serializer\Encoder;

use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;

/**
 * Encodes CSV data.
 *
 * @author Kévin Dunglas <dunglas@gmail.com>
 * @author Oliver Hoff <oliver@hofff.com>
 */
class CsvEncoder implements EncoderInterface, DecoderInterface
{
    public const FORMAT = 'csv';
    public const DELIMITER_KEY = 'csv_delimiter';
    public const ENCLOSURE_KEY = 'csv_enclosure';
    public const ESCAPE_CHAR_KEY = 'csv_escape_char';
    public const KEY_SEPARATOR_KEY = 'csv_key_separator';
    public const HEADERS_KEY = 'csv_headers';
    public const ESCAPE_FORMULAS_KEY = 'csv_escape_formulas';
    public const AS_COLLECTION_KEY = 'as_collection';
    public const NO_HEADERS_KEY = 'no_headers';
    public const END_OF_LINE = 'csv_end_of_line';
    public const OUTPUT_UTF8_BOM_KEY = 'output_utf8_bom';
    public const CHECK_VALID_HEADERS = 'check_valid_headers';

    private const UTF8_BOM = "\xEF\xBB\xBF";

    private const FORMULAS_START_CHARACTERS = ['=', '-', '+', '@', "\t", "\r"];

    private $defaultContext = [
        self::DELIMITER_KEY => ',',
        self::ENCLOSURE_KEY => '"',
        self::ESCAPE_CHAR_KEY => '',
        self::END_OF_LINE => "\n",
        self::ESCAPE_FORMULAS_KEY => false,
        self::HEADERS_KEY => [],
        self::KEY_SEPARATOR_KEY => '.',
        self::NO_HEADERS_KEY => false,
        self::AS_COLLECTION_KEY => true,
        self::OUTPUT_UTF8_BOM_KEY => false,
        self::CHECK_VALID_HEADERS => false,
    ];

    public function __construct(array $defaultContext = [])
    {
        $this->defaultContext = array_merge($this->defaultContext, $defaultContext);
    }

    /**
     * {@inheritdoc}
     */
    public function encode(mixed $data, string $format, array $context = []): string
    {
        $handle = fopen('php://temp,', 'w+');

        if (!is_iterable($data)) {
            $data = [[$data]];
        } elseif (empty($data)) {
            $data = [[]];
        } else {
            // Sequential arrays of arrays are considered as collections
            $i = 0;
            foreach ($data as $key => $value) {
                if ($i !== $key || !\is_array($value)) {
                    $data = [$data];
                    break;
                }

                ++$i;
            }
        }

        [$delimiter, $enclosure, $escapeChar, $keySeparator, $headers, $escapeFormulas, $outputBom] = $this->getCsvOptions($context);

        foreach ($data as &$value) {
            $flattened = [];
            $this->flatten($value, $flattened, $keySeparator, '', $escapeFormulas);
            $value = $flattened;
        }
        unset($value);

        $headers = array_merge(array_values($headers), array_diff($this->extractHeaders($data), $headers));
        $endOfLine = $context[self::END_OF_LINE] ?? $this->defaultContext[self::END_OF_LINE];

        if (!($context[self::NO_HEADERS_KEY] ?? $this->defaultContext[self::NO_HEADERS_KEY])) {
            fputcsv($handle, $headers, $delimiter, $enclosure, $escapeChar);
            if ("\n" !== $endOfLine && 0 === fseek($handle, -1, \SEEK_CUR)) {
                fwrite($handle, $endOfLine);
            }
        }

        $headers = array_fill_keys($headers, '');
        foreach ($data as $row) {
            fputcsv($handle, array_replace($headers, $row), $delimiter, $enclosure, $escapeChar);
            if ("\n" !== $endOfLine && 0 === fseek($handle, -1, \SEEK_CUR)) {
                fwrite($handle, $endOfLine);
            }
        }

        rewind($handle);
        $value = stream_get_contents($handle);
        fclose($handle);

        if ($outputBom) {
            if (!preg_match('//u', $value)) {
                throw new UnexpectedValueException('You are trying to add a UTF-8 BOM to a non UTF-8 text.');
            }

            $value = self::UTF8_BOM.$value;
        }

        return $value;
    }

    /**
     * {@inheritdoc}
     *
     * @param array $context
     */
    public function supportsEncoding(string $format /*, array $context = [] */): bool
    {
        return self::FORMAT === $format;
    }

    /**
     * {@inheritdoc}
     */
    public function decode(string $data, string $format, array $context = []): mixed
    {
        $handle = fopen('php://temp', 'r+');
        fwrite($handle, $data);
        rewind($handle);

        if (str_starts_with($data, self::UTF8_BOM)) {
            fseek($handle, \strlen(self::UTF8_BOM));
        }

        $headers = null;
        $nbHeaders = 0;
        $headerCount = [];
        $result = [];

        [$delimiter, $enclosure, $escapeChar, $keySeparator, $expectedHeaders, , , $asCollection] = $this->getCsvOptions($context);

        while (false !== ($cols = fgetcsv($handle, 0, $delimiter, $enclosure, $escapeChar))) {
            $nbCols = \count($cols);

            if (null === $headers) {
                $nbHeaders = $nbCols;

                if ($context[self::NO_HEADERS_KEY] ?? $this->defaultContext[self::NO_HEADERS_KEY]) {
                    for ($i = 0; $i < $nbCols; ++$i) {
                        $headers[] = [$i];
                    }
                    $headerCount = array_fill(0, $nbCols, 1);
                } else {
                    foreach ($cols as $col) {
                        $header = explode($keySeparator, $col);
                        $headers[] = $header;
                        $headerCount[] = \count($header);
                    }

                    if (($context[self::CHECK_VALID_HEADERS] ?? $this->defaultContext[self::CHECK_VALID_HEADERS])
                        && \count($cols) !== \count($expectedHeaders)
                        && \count(array_intersect($cols, $expectedHeaders)) !== \count($expectedHeaders)
                    ) {
                        throw new NotEncodableValueException(
                            sprintf(
                                'Expected %s headers, got %s.',
                                implode(',', $expectedHeaders),
                                implode(',', $cols)
                            )
                        );
                    }

                    continue;
                }
            }

            $item = [];
            for ($i = 0; ($i < $nbCols) && ($i < $nbHeaders); ++$i) {
                $depth = $headerCount[$i];
                $arr = &$item;
                for ($j = 0; $j < $depth; ++$j) {
                    // Handle nested arrays
                    if ($j === ($depth - 1)) {
                        $arr[$headers[$i][$j]] = $cols[$i];

                        continue;
                    }

                    if (!isset($arr[$headers[$i][$j]])) {
                        $arr[$headers[$i][$j]] = [];
                    }

                    $arr = &$arr[$headers[$i][$j]];
                }
            }

            $result[] = $item;
        }
        fclose($handle);

        if ($asCollection) {
            return $result;
        }

        if (empty($result) || isset($result[1])) {
            return $result;
        }

        // If there is only one data line in the document, return it (the line), the result is not considered as a collection
        return $result[0];
    }

    /**
     * {@inheritdoc}
     *
     * @param array $context
     */
    public function supportsDecoding(string $format /*, array $context = [] */): bool
    {
        return self::FORMAT === $format;
    }

    /**
     * Flattens an array and generates keys including the path.
     */
    private function flatten(iterable $array, array &$result, string $keySeparator, string $parentKey = '', bool $escapeFormulas = false)
    {
        foreach ($array as $key => $value) {
            if (is_iterable($value)) {
                $this->flatten($value, $result, $keySeparator, $parentKey.$key.$keySeparator, $escapeFormulas);
            } else {
                if ($escapeFormulas && \in_array(substr((string) $value, 0, 1), self::FORMULAS_START_CHARACTERS, true)) {
                    $result[$parentKey.$key] = "'".$value;
                } else {
                    // Ensures an actual value is used when dealing with true and false
                    $result[$parentKey.$key] = false === $value ? 0 : (true === $value ? 1 : $value);
                }
            }
        }
    }

    private function getCsvOptions(array $context): array
    {
        $delimiter = $context[self::DELIMITER_KEY] ?? $this->defaultContext[self::DELIMITER_KEY];
        $enclosure = $context[self::ENCLOSURE_KEY] ?? $this->defaultContext[self::ENCLOSURE_KEY];
        $escapeChar = $context[self::ESCAPE_CHAR_KEY] ?? $this->defaultContext[self::ESCAPE_CHAR_KEY];
        $keySeparator = $context[self::KEY_SEPARATOR_KEY] ?? $this->defaultContext[self::KEY_SEPARATOR_KEY];
        $headers = $context[self::HEADERS_KEY] ?? $this->defaultContext[self::HEADERS_KEY];
        $escapeFormulas = $context[self::ESCAPE_FORMULAS_KEY] ?? $this->defaultContext[self::ESCAPE_FORMULAS_KEY];
        $outputBom = $context[self::OUTPUT_UTF8_BOM_KEY] ?? $this->defaultContext[self::OUTPUT_UTF8_BOM_KEY];
        $asCollection = $context[self::AS_COLLECTION_KEY] ?? $this->defaultContext[self::AS_COLLECTION_KEY];

        if (!\is_array($headers)) {
            throw new InvalidArgumentException(sprintf('The "%s" context variable must be an array or null, given "%s".', self::HEADERS_KEY, get_debug_type($headers)));
        }

        return [$delimiter, $enclosure, $escapeChar, $keySeparator, $headers, $escapeFormulas, $outputBom, $asCollection];
    }

    /**
     * @return string[]
     */
    private function extractHeaders(iterable $data): array
    {
        $headers = [];
        $flippedHeaders = [];

        foreach ($data as $row) {
            $previousHeader = null;

            foreach ($row as $header => $_) {
                if (isset($flippedHeaders[$header])) {
                    $previousHeader = $header;
                    continue;
                }

                if (null === $previousHeader) {
                    $n = \count($headers);
                } else {
                    $n = $flippedHeaders[$previousHeader] + 1;

                    for ($j = \count($headers); $j > $n; --$j) {
                        ++$flippedHeaders[$headers[$j] = $headers[$j - 1]];
                    }
                }

                $headers[$n] = $header;
                $flippedHeaders[$header] = $n;
                $previousHeader = $header;
            }
        }

        return $headers;
    }
}
