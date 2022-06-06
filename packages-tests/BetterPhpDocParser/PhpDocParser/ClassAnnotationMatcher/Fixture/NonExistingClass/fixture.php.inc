<?php

namespace Rector\Tests\BetterPhpDocParser\PhpDocParser\ClassAnnotationMatcher\Fixture\NonExistingClass;

use My\Namespace\OtherUnknownClass;
use My\Namespace\OtherUnknownClass as AliasedUnknownClass;

class MyClass
{
    /** @var UnknownClass */
    private $unknownClass;
    /** @var \My\Namespace\UnknownClass */
    private $unknownNamespacedClass;
    /** @var OtherUnknownClass */
    private $unknownUsedClass;
    /** @var AliasedUnknownClass */
    private $unknownAliasedUsedClass;
    /** @var \UnknownGlobalClass */
    private $unknownGlobalClass;
}
