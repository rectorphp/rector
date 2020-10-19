<?php

declare(strict_types=1);

namespace Rector\DeadCode\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Core\Reflection\ClassReflectionToAstResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see \Rector\DeadCode\Tests\Rector\MethodCall\RemoveEmptyMethodCallRector\RemoveEmptyMethodCallRectorTest
 */
final class RemoveEmptyMethodCallRector extends AbstractRector
{
    /**
     * @var ClassReflectionToAstResolver
     */
    private $classReflectionToAstResolver;

    public function __construct(ClassReflectionToAstResolver $classReflectionToAstResolver)
    {
        $this->classReflectionToAstResolver = $classReflectionToAstResolver;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Remove empty method call', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function callThis()
    {
    }
}

$some = new SomeClass();
$some->callThis();
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function callThis()
    {
    }
}

$some = new SomeClass();
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class];
    }

    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        /** @var Scope|null $scope */
        $scope = $node->var->getAttribute(AttributeKey::SCOPE);
        if ($scope === null) {
            return null;
        }

        $type = $scope->getType($node->var);
        if (! $type instanceof ObjectType) {
            return null;
        }

        $class = $this->classReflectionToAstResolver->getClassFromObjectType($type);
        if ($class === null) {
            return null;
        }

        if (! $this->isEmptyMethod($class, $node)) {
            return null;
        }

        $this->removeNode($node);

        return $node;
    }

    private function isEmptyMethod(Class_ $class, MethodCall $methodCall): bool
    {
        $methodName = $this->getName($methodCall->name);
        if ($methodName === null) {
            return false;
        }

        $classMethod = $class->getMethod($methodName);
        if ($classMethod === null) {
            return false;
        }

        return count((array) $classMethod->stmts) === 0;
    }
}
