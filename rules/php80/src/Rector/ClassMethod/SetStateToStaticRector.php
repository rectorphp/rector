<?php

declare(strict_types=1);

namespace Rector\Php80\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\MethodName;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Php80\Tests\Rector\ClassMethod\SetStateToStaticRector\SetStateToStaticRectorTest
 */
final class SetStateToStaticRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Adds static visibility to __set_state() methods', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function __set_state($properties) {

    }
}
CODE_SAMPLE
,
                <<<'CODE_SAMPLE'
class SomeClass
{
    public static function __set_state($properties) {

    }
}
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class];
    }

    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }

        $this->visibilityManipulator->makeStatic($node);

        return $node;
    }

    private function shouldSkip(ClassMethod $classMethod): bool
    {
        if (! $this->isName($classMethod, MethodName::SET_STATE)) {
            return true;
        }
        return $classMethod->isStatic();
    }
}
