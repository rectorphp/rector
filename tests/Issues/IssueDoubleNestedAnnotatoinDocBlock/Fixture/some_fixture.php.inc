<?php

namespace Rector\Core\Tests\Issues\IssueDoubleNestedAnnotatoinDocBlock\Fixture;

use Rector\Core\Tests\Issues\IssueDoubleNestedAnnotatoinDocBlock\Source\SomeAnnotation;

/**
 * @SomeAnnotation(
 *     @SomeAnnotation(key="value")
 * )
 */
final class SomeFixture
{
}
