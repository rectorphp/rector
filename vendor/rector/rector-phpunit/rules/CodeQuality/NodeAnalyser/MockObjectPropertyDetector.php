<?php

declare (strict_types=1);
namespace Rector\PHPUnit\CodeQuality\NodeAnalyser;

use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Property;
use Rector\PHPUnit\Enum\PHPUnitClassName;
final class MockObjectPropertyDetector
{
    public function detect(Property $property): bool
    {
        if (!$property->type instanceof FullyQualified) {
            return \false;
        }
        return $property->type->toString() === PHPUnitClassName::MOCK_OBJECT;
    }
}
