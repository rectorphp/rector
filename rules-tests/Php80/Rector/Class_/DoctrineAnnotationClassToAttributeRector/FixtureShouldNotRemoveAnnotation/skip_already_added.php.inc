<?php

namespace Rector\Tests\Php80\Rector\Class_\DoctrineAnnotationClassToAttributeRector\FixtureShouldNotRemoveAnnotation;

use Attribute;

/**
 * @Annotation
 */
#[Attribute]
final class SkipAlreadyAdded
{
}
