<?php

declare(strict_types=1);

namespace Rector\Core\Rector;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\NodeTypeResolver\Node\AttributeKey;

abstract class AbstractPHPUnitRector extends AbstractRector
{
    /**
     * @var string
     * @see https://regex101.com/r/76ZfTX/1
     */
    private const TEST_ANNOTATOIN_REGEX = '#@test\b#';

    protected function isTestClassMethod(ClassMethod $classMethod): bool
    {
        if (! $classMethod->isPublic()) {
            return false;
        }

        if ($this->isName($classMethod, 'test*')) {
            return true;
        }

        $docComment = $classMethod->getDocComment();
        if ($docComment !== null) {
            return (bool) Strings::match($docComment->getText(), self::TEST_ANNOTATOIN_REGEX);
        }

        return false;
    }

    protected function isPHPUnitMethodName(Node $node, string $name): bool
    {
        if (! $this->isPHPUnitTestCaseCall($node)) {
            return false;
        }

        /** @var StaticCall|MethodCall $node */
        return $this->isName($node->name, $name);
    }

    /**
     * @param string[] $names
     */
    protected function isPHPUnitMethodNames(Node $node, array $names): bool
    {
        if (! $this->isPHPUnitTestCaseCall($node)) {
            return false;
        }

        /** @var MethodCall|StaticCall $node */
        return $this->isNames($node->name, $names);
    }

    protected function isInTestClass(Node $node): bool
    {
        $classLike = $node->getAttribute(AttributeKey::CLASS_NODE);
        if ($classLike === null) {
            return false;
        }

        return $this->isObjectTypes($classLike, ['PHPUnit\Framework\TestCase', 'PHPUnit_Framework_TestCase']);
    }

    /**
     * @param StaticCall|MethodCall $node
     * @return StaticCall|MethodCall
     */
    protected function createPHPUnitCallWithName(Node $node, string $name): Node
    {
        return $node instanceof MethodCall ? new MethodCall($node->var, $name) : new StaticCall($node->class, $name);
    }

    protected function isPHPUnitTestCaseCall(Node $node): bool
    {
        if (! $this->isInTestClass($node)) {
            return false;
        }

        return $node instanceof MethodCall || $node instanceof StaticCall;
    }
}
