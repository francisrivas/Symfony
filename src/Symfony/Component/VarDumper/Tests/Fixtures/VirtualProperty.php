<?php

namespace Symfony\Component\VarDumper\Tests\Fixtures;

class VirtualProperty
{
    public string $firstName = 'John';
    public string $lastName = 'Doe';

    public string $fullName {
        get {
            return $this->firstName.' '.$this->lastName;
        }
    }

    public $noType {
        get {
            return null;
        }
    }
}
