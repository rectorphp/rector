<?php

declare (strict_types=1);
namespace RectorPrefix202608\Entropy\Attributes;

use Attribute;
use PHPUnit\Framework\TestCase;
#[Attribute(Attribute::TARGET_CLASS)]
final class RelatedTest
{
    /**
     * @param class-string<TestCase> $testClass
     */
    public function __construct(string $testClass)
    {
    }
}
