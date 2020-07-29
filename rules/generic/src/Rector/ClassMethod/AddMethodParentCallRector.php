<?php

declare(strict_types=1);

namespace Rector\Generic\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see \Rector\Generic\Tests\Rector\ClassMethod\AddMethodParentCallRector\AddMethodParentCallRectorTest
 */
final class AddMethodParentCallRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const METHODS_BY_PARENT_TYPES = '$methodsByParentTypes';

    /**
     * @var mixed[]
     */
    private $methodsByParentTypes = [];

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Add method parent call, in case new parent method is added',
            [
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
            ]
        );
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
        $classLike = $node->getAttribute(AttributeKey::CLASS_NODE);
        if ($classLike === null) {
            return null;
        }

        /** @var string $className */
        $className = $node->getAttribute(AttributeKey::CLASS_NAME);

        foreach ($this->methodsByParentTypes as $type => $methods) {
            if (! $this->isObjectType($classLike, $type)) {
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

    public function configure(array $configuration): void
    {
        $this->methodsByParentTypes = $configuration[self::METHODS_BY_PARENT_TYPES] ?? [];
    }

    private function shouldSkipMethod(ClassMethod $classMethod, string $method): bool
    {
        if (! $this->isName($classMethod, $method)) {
            return true;
        }

        return $this->hasParentCallOfMethod($classMethod, $method);
    }

    private function createParentStaticCall(string $method): Expression
    {
        $staticCall = new StaticCall(new Name('parent'), new Identifier($method));

        return new Expression($staticCall);
    }

    /**
     * Looks for "parent::<methodName>
     */
    private function hasParentCallOfMethod(ClassMethod $classMethod, string $method): bool
    {
        return (bool) $this->betterNodeFinder->findFirst((array) $classMethod->stmts, function (Node $node) use (
            $method
        ): bool {
            return $this->isStaticCallNamed($node, 'parent', $method);
        });
    }
}
