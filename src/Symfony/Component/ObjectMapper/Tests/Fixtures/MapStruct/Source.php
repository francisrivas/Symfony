<?php

namespace Symfony\Component\ObjectMapper\Tests\Fixtures\MapStruct;

class Source
{
    public function __construct(public readonly string $propertyA, public readonly string $propertyB, public readonly string $propertyC) {}
}
