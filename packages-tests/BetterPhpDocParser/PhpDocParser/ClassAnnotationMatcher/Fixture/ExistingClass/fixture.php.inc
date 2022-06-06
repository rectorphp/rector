<?php

namespace Rector\Tests\BetterPhpDocParser\PhpDocParser\ClassAnnotationMatcher\Fixture\ExistingClass;

use Rector\Tests\BetterPhpDocParser\PhpDocParser\ClassAnnotationMatcher\Source\KnownClass;
use Rector\Tests\BetterPhpDocParser\PhpDocParser\ClassAnnotationMatcher\Source\KnownClass as AliasedKnownClass;
use SplHeap as AliasedSplHeap;

class MyClass
{
    /** @var KnownClass */
    private $knownClass;
    /** @var \Rector\Tests\BetterPhpDocParser\PhpDocParser\ClassAnnotationMatcher\Source\KnownClass */
    private $knownInlinedClass;
    /** @var SiblingClass */
    private $knownSiblingClass;
    /** @var AliasedKnownClass */
    private $knownAliasedClass;
    /** @var \SplHeap */
    private $knownGlobalClass;
    /** @var AliasedSplHeap */
    private $knownAliasedGlobalClass;
}
