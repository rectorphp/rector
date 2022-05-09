<?php

namespace Rector\Tests\CodingStyle\Rector\ClassMethod\OrderAttributesRector\SpecificOrder\Fixture;

use Rector\Tests\CodingStyle\Rector\ClassMethod\OrderAttributesRector\SpecificOrder\Source\FirstAttribute;
use Rector\Tests\CodingStyle\Rector\ClassMethod\OrderAttributesRector\SpecificOrder\Source\SecondAttribute;
use Rector\Tests\CodingStyle\Rector\ClassMethod\OrderAttributesRector\SpecificOrder\Source\ThirdAttribute;

#[ThirdAttribute]
#[FirstAttribute, SecondAttribute]
final class FirstFromMultipleAttrInAttrGroups
{
}

?>
-----
<?php

namespace Rector\Tests\CodingStyle\Rector\ClassMethod\OrderAttributesRector\SpecificOrder\Fixture;

use Rector\Tests\CodingStyle\Rector\ClassMethod\OrderAttributesRector\SpecificOrder\Source\FirstAttribute;
use Rector\Tests\CodingStyle\Rector\ClassMethod\OrderAttributesRector\SpecificOrder\Source\SecondAttribute;
use Rector\Tests\CodingStyle\Rector\ClassMethod\OrderAttributesRector\SpecificOrder\Source\ThirdAttribute;

#[FirstAttribute, SecondAttribute]
#[ThirdAttribute]
final class FirstFromMultipleAttrInAttrGroups
{
}

?>
