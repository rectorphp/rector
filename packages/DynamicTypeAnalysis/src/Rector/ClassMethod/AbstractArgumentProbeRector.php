<?php

declare(strict_types=1);

namespace Rector\DynamicTypeAnalysis\Rector\ClassMethod;

use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Interface_;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\PhpVersionFeature;

/**
 * @see \Rector\DynamicTypeAnalysis\Tests\Rector\ClassMethod\AddArgumentTypeWithProbeDataRector\AddArgumentTypeWithProbeDataRectorTest
 */
abstract class AbstractArgumentProbeRector extends AbstractRector
{
    protected function shouldSkipClassMethod(ClassMethod $classMethod): bool
    {
        // we need at least scalar types to make this work
        if (! $this->isAtLeastPhpVersion(PhpVersionFeature::SCALAR_TYPES)) {
            return true;
        }

        $classNode = $classMethod->getAttribute(AttributeKey::CLASS_NODE);

        // skip interfaces
        if ($classNode instanceof Interface_) {
            return true;
        }

        // only record public methods = they're the entry points + prevent duplicated type recording
        if (! $classMethod->isPublic()) {
            return true;
        }

        // we need some params to analyze
        if (count((array) $classMethod->params) === 0) {
            return true;
        }

        // method without body doesn't need analysis
        return count((array) $classMethod->stmts) === 0;
    }

    protected function getClassMethodReference(ClassMethod $classMethod): ?string
    {
        $className = $classMethod->getAttribute(AttributeKey::CLASS_NAME);
        if ($className === null) {
            return null;
        }

        $methodName = $this->getName($classMethod->name);
        if ($methodName === null) {
            return null;
        }

        return $className . '::' . $methodName;
    }
}
