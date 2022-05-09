<?php

namespace Rector\Tests\CodingStyle\Rector\ClassMethod\OrderAttributesRector\SpecificOrder\Fixture;

use Rector\Tests\CodingStyle\Rector\ClassMethod\OrderAttributesRector\SpecificOrder\Source\FirstAttribute;
use Rector\Tests\CodingStyle\Rector\ClassMethod\OrderAttributesRector\SpecificOrder\Source\SecondAttribute;

#[FirstAttribute]
#[SecondAttribute]
class SkipExpectedOrder
{
}
