<?php

namespace Rector\Tests\CodingStyle\Rector\ClassMethod\MakeInheritedMethodVisibilitySameAsParentRector\Fixture;

use Rector\Tests\CodingStyle\Rector\ClassMethod\MakeInheritedMethodVisibilitySameAsParentRector\Source\NoMethodAnnotationHere;

/**
 * @method some()
 */
final class SkipMethodAnnotation extends NoMethodAnnotationHere
{
}
