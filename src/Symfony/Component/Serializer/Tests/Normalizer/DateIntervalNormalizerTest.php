<?php

namespace Symfony\Component\Serializer\Tests\Normalizer;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\DateIntervalNormalizer;

/**
 * @author Jérôme Parmentier <jerome@prmntr.me>
 */
class DateIntervalNormalizerTest extends TestCase
{
    /**
     * @var DateIntervalNormalizer
     */
    private $normalizer;

    protected function setUp()
    {
        $this->normalizer = new DateIntervalNormalizer();
    }

    public function dataProviderISO()
    {
        $data = array(
            array('P%YY%MM%DDT%HH%IM%SS', 'P00Y00M00DT00H00M00S', 'PT0S'),
            array('P%yY%mM%dDT%hH%iM%sS', 'P0Y0M0DT0H0M0S', 'PT0S'),
            array('P%yY%mM%dDT%hH%iM%sS', 'P10Y2M3DT16H5M6S', 'P10Y2M3DT16H5M6S'),
            array('P%yY%mM%dDT%hH%iM', 'P10Y2M3DT16H5M', 'P10Y2M3DT16H5M'),
            array('P%yY%mM%dDT%hH', 'P10Y2M3DT16H', 'P10Y2M3DT16H'),
            array('P%yY%mM%dD', 'P10Y2M3D', 'P10Y2M3DT0H'),
        );

        return $data;
    }

    public function dataProviderDate()
    {
        $data = array(
            array(
                '%y years %m months %d days %h hours %i minutes %s seconds',
                '10 years 2 months 3 days 16 hours 5 minutes 6 seconds',
                'P10Y2M3DT16H5M6S',
            ),
            array(
                '%y years %m months %d days %h hours %i minutes',
                '10 years 2 months 3 days 16 hours 5 minutes',
                'P10Y2M3DT16H5M',
            ),
            array('%y years %m months %d days %h hours', '10 years 2 months 3 days 16 hours', 'P10Y2M3DT16H'),
            array('%y years %m months %d days', '10 years 2 months 3 days', 'P10Y2M3D'),
            array('%y years %m months', '10 years 2 months', 'P10Y2M'),
            array('%y year', '1 year', 'P1Y'),
        );

        return $data;
    }

    public function testSupportsNormalization()
    {
        $this->assertTrue($this->normalizer->supportsNormalization(new \DateInterval('P00Y00M00DT00H00M00S')));
        $this->assertFalse($this->normalizer->supportsNormalization(new \stdClass()));
    }

    public function testNormalize()
    {
        $this->assertEquals('P0Y0M0DT0H0M0S', $this->normalizer->normalize(new \DateInterval('PT0S')));
    }

    /**
     * @dataProvider dataProviderISO
     */
    public function testNormalizeUsingFormatPassedInContext($format, $output, $input)
    {
        $this->assertEquals($output, $this->normalizer->normalize(new \DateInterval($input), null, array(DateIntervalNormalizer::FORMAT_KEY => $format)));
    }

    /**
     * @dataProvider dataProviderISO
     */
    public function testNormalizeUsingFormatPassedInConstructor($format, $output, $input)
    {
        $this->assertEquals($output, (new DateIntervalNormalizer($format))->normalize(new \DateInterval($input)));
    }

    /**
     * @expectedException \Symfony\Component\Serializer\Exception\InvalidArgumentException
     * @expectedExceptionMessage The object must be an instance of "\DateInterval".
     */
    public function testNormalizeInvalidObjectThrowsException()
    {
        $this->normalizer->normalize(new \stdClass());
    }

    public function testSupportsDenormalization()
    {
        $this->assertTrue($this->normalizer->supportsDenormalization('P00Y00M00DT00H00M00S', \DateInterval::class));
        $this->assertFalse($this->normalizer->supportsDenormalization('foo', 'Bar'));
    }

    public function testDenormalize()
    {
        $this->assertDateIntervalEquals(new \DateInterval('P00Y00M00DT00H00M00S'), $this->normalizer->denormalize('P00Y00M00DT00H00M00S', \DateInterval::class));
    }

    /**
     * @dataProvider dataProviderISO
     */
    public function testDenormalizeUsingFormatPassedInContext($format, $input, $output)
    {
        $this->assertDateIntervalEquals(new \DateInterval($output), $this->normalizer->denormalize($input, \DateInterval::class, null, array(DateIntervalNormalizer::FORMAT_KEY => $format)));
    }

    /**
     * @dataProvider dataProviderISO
     */
    public function testDenormalizeUsingFormatPassedInConstructor($format, $input, $output)
    {
        $this->assertDateIntervalEquals(new \DateInterval($output), (new DateIntervalNormalizer($format))->denormalize($input, \DateInterval::class));
    }

    /**
     * @expectedException \Symfony\Component\Serializer\Exception\InvalidArgumentException
     */
    public function testDenormalizeExpectsString()
    {
        $this->normalizer->denormalize(1234, \DateInterval::class);
    }

    /**
     * @dataProvider dataProviderDate
     * @expectedException \Symfony\Component\Serializer\Exception\UnexpectedValueException
     * @expectedExceptionMessage Non ISO 8601 interval strings are not supported yet.
     */
    public function testDenormalizeNonISO8601IntervalStringThrowsException($format, $input, $output)
    {
        $this->assertEquals(new \DateInterval($output), $this->normalizer->denormalize($input, \DateInterval::class, null, array(DateIntervalNormalizer::FORMAT_KEY => $format)));
    }

    /**
     * @expectedException \Symfony\Component\Serializer\Exception\UnexpectedValueException
     */
    public function testDenormalizeInvalidDataThrowsException()
    {
        $this->normalizer->denormalize('invalid interval', \DateInterval::class);
    }

    /**
     * @expectedException \Symfony\Component\Serializer\Exception\UnexpectedValueException
     */
    public function testDenormalizeFormatMismatchThrowsException()
    {
        $this->normalizer->denormalize('P00Y00M00DT00H00M00S', \DateInterval::class, null, array(DateIntervalNormalizer::FORMAT_KEY => 'P10Y2M3D'));
    }

    private function assertDateIntervalEquals(\DateInterval $expected, \DateInterval $actual)
    {
        $this->assertEquals($expected->format('%RP%yY%mM%dDT%hH%iM%sS'), $actual->format('%RP%yY%mM%dDT%hH%iM%sS'));
    }
}
