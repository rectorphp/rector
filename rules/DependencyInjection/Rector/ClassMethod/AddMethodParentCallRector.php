<?php

declare(strict_types=1);

namespace Rector\DependencyInjection\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PHPStan\Type\ObjectType;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\MethodName;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Tests\DependencyInjection\Rector\ClassMethod\AddMethodParentCallRector\AddMethodParentCallRectorTest
 */
final class AddMethodParentCallRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const METHODS_BY_PARENT_TYPES = 'methods_by_parent_type';

    /**
     * @var array<string, string>
     */
    private array $methodByParentTypes = [];

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Add method parent call, in case new parent method is added',
            [
                new ConfiguredCodeSample(
                    <<<'CODE_SAMPLE'
class SunshineCommand extends ParentClassWithNewConstructor
{
    public function __construct()
    {
        $value = 5;
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
class SunshineCommand extends ParentClassWithNewConstructor
{
    public function __construct()
    {
        $value = 5;

        parent::__construct();
    }
}
CODE_SAMPLE
                    ,
                    [
                        self::METHODS_BY_PARENT_TYPES => [
                            'ParentClassWithNewConstructor' => MethodName::CONSTRUCT,
                        ],
                    ]
                ),
            ]
        );
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
        $classLike = $node->getAttribute(AttributeKey::CLASS_NODE);
        if (! $classLike instanceof ClassLike) {
            return null;
        }

        /** @var string $className */
        $className = $node->getAttribute(AttributeKey::CLASS_NAME);

        foreach ($this->methodByParentTypes as $type => $method) {
            if (! $this->isObjectType($classLike, new ObjectType($type))) {
                continue;
            }

            // not itself
            if ($className === $type) {
                continue;
            }

            if ($this->shouldSkipMethod($node, $method)) {
                continue;
            }

            $node->stmts[] = $this->createParentStaticCall($method);

            return $node;
        }

        return null;
    }

    /**
     * @param array<string, array<string, string>> $configuration
     */
    public function configure(array $configuration): void
    {
        $this->methodByParentTypes = $configuration[self::METHODS_BY_PARENT_TYPES] ?? [];
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
        $staticCall = $this->nodeFactory->createStaticCall('parent', $method);
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
            if (! $node instanceof StaticCall) {
                return false;
            }

            if (! $this->isName($node->class, 'parent')) {
                return false;
            }

            return $this->isName($node->name, $method);
        });
    }
}
