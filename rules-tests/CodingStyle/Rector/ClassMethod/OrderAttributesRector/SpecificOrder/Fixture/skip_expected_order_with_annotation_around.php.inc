<?php

namespace Rector\Tests\CodingStyle\Rector\ClassMethod\OrderAttributesRector\SpecificOrder\Fixture;

use Rector\Tests\CodingStyle\Rector\ClassMethod\OrderAttributesRector\SpecificOrder\Source\FirstAttribute;
use Rector\Tests\CodingStyle\Rector\ClassMethod\OrderAttributesRector\SpecificOrder\Source\SecondAttribute;

/**
 * @some
 */
#[FirstAttribute]
#[SecondAttribute]
class SkipExpectedOrderWithAround
{
}
