<?php

declare(strict_types=1);

namespace Rector\DeadCode\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\MethodName;
use Rector\DeadCode\NodeAnalyzer\IsClassMethodUsedAnalyzer;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUnusedPrivateMethodRector\RemoveUnusedPrivateMethodRectorTest
 */
final class RemoveUnusedPrivateMethodRector extends AbstractRector
{
    public function __construct(
        private IsClassMethodUsedAnalyzer $isClassMethodUsedAnalyzer
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove unused private method', [
            new CodeSample(
                <<<'CODE_SAMPLE'
final class SomeController
{
    public function run()
    {
        return 5;
    }

    private function skip()
    {
        return 10;
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
final class SomeController
{
    public function run()
    {
        return 5;
    }
}
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return array<class-string<Node>>
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

        if ($this->isClassMethodUsedAnalyzer->isClassMethodUsed($node)) {
            return null;
        }

        $this->removeNode($node);

        return $node;
    }

    private function shouldSkip(ClassMethod $classMethod): bool
    {
        $scope = $classMethod->getAttribute(AttributeKey::SCOPE);
        if (! $scope instanceof Scope) {
            return true;
        }

        $classReflection = $scope->getClassReflection();
        if (! $classReflection instanceof ClassReflection) {
            return true;
        }

        // unreliable to detect trait, interface doesn't make sense
        if ($classReflection->isTrait()) {
            return true;
        }

        if ($classReflection->isInterface()) {
            return true;
        }

        if ($classReflection->isAnonymous()) {
            return true;
        }

        // skips interfaces by default too
        if (! $classMethod->isPrivate()) {
            return true;
        }

        // skip magic methods - @see https://www.php.net/manual/en/language.oop5.magic.php
        if ($classMethod->isMagic()) {
            return true;
        }

        return $classReflection->hasMethod(MethodName::CALL);
    }
}
