<?php

declare(strict_types=1);

namespace Rector\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\Tests\Rector\ClassMethod\AddMethodParentCallRector\AddMethodParentCallRectorTest
 */
final class AddMethodParentCallRector extends AbstractRector
{
    /**
     * @var mixed[]
     */
    private $methodsByParentTypes = [];

    /**
     * @param mixed[] $methodsByParentTypes
     */
    public function __construct(array $methodsByParentTypes = [])
    {
        $this->methodsByParentTypes = $methodsByParentTypes;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Add method parent call, in case new parent method is added', [
            new CodeSample(
                <<<'PHP'
class SunshineCommand extends ParentClassWithNewConstructor
{
    public function __construct()
    {
        $value = 5;
    }
}
PHP
                ,
                <<<'PHP'
class SunshineCommand extends ParentClassWithNewConstructor
{
    public function __construct()
    {
        $value = 5;

        parent::__construct();
    }
}
PHP
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
        $class = $node->getAttribute(AttributeKey::CLASS_NODE);
        if ($class === null) {
            return null;
        }

        /** @var string $className */
        $className = $node->getAttribute(AttributeKey::CLASS_NAME);

        foreach ($this->methodsByParentTypes as $type => $methods) {
            if (! $this->isObjectType($class, $type)) {
                continue;
            }

            // not itself
            if ($className === $type) {
                continue;
            }

            foreach ($methods as $method) {
                if ($this->shouldSkipMethod($node, $method)) {
                    continue;
                }

                $node->stmts[] = $this->createParentStaticCall($method);

                return $node;
            }
        }

        return null;
    }

    /**
     * Looks for "parent::<methodName>
     */
    private function hasParentCallOfMethod(ClassMethod $classMethod, string $method): bool
    {
        return (bool) $this->betterNodeFinder->findFirst((array) $classMethod->stmts, function (Node $node) use (
            $method
        ): bool {
            if (! $node instanceof StaticCall) {
                return false;
            }

            if (! $this->isName($node->class, 'parent')) {
                return false;
            }

            return $this->isName($node, $method);
        });
    }

    private function createParentStaticCall(string $method): Expression
    {
        $parentStaticCall = new StaticCall(new Name('parent'), new Identifier($method));

        return new Expression($parentStaticCall);
    }

    private function shouldSkipMethod(ClassMethod $classMethod, string $method): bool
    {
        if (! $this->isName($classMethod, $method)) {
            return true;
        }

        return $this->hasParentCallOfMethod($classMethod, $method);
    }
}
