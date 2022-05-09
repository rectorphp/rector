<?php

namespace Rector\Tests\CodingStyle\Rector\ClassMethod\OrderAttributesRector\AlphabeticallOrder\Fixture;

use Rector\Tests\CodingStyle\Rector\ClassMethod\OrderAttributesRector\AlphabeticallOrder\Source\AAttribute;
use Rector\Tests\CodingStyle\Rector\ClassMethod\OrderAttributesRector\AlphabeticallOrder\Source\BAttribute;
use Rector\Tests\CodingStyle\Rector\ClassMethod\OrderAttributesRector\AlphabeticallOrder\Source\CAttribute;

#[AAttribute]
#[BAttribute]
#[CAttribute]
class SkipExpectedOrderWithAround
{
}
