<?php

declare(strict_types=1);

namespace Rector\DowngradePhp74\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Scalar\String_;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Tests\DowngradePhp74\Rector\MethodCall\DowngradeReflectionGetTypeRector\DowngradeReflectionGetTypeRectorTest
 */
final class DowngradeReflectionGetTypeRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Downgrade reflection $refleciton->getType() method call', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run(ReflectionProperty $reflectionProperty)
    {
        if ($reflectionProperty->getType()) {
            return true;
        }

        return false;
    }
}
CODE_SAMPLE

                ,
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run(ReflectionProperty $reflectionProperty)
    {
        if (null) {
            return true;
        }

        return false;
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
        return [MethodCall::class];
    }

    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isName($node->name, 'getType')) {
            return null;
        }

        if (! $this->isObjectType($node->var, new ObjectType('ReflectionProperty'))) {
            return null;
        }

        $parent = $node->getAttribute(AttributeKey::PARENT_NODE);
        if ($parent instanceof Instanceof_) {
            return null;
        }

        // avoid infinite loop
        $createdByRule = $node->getAttribute(AttributeKey::CREATED_BY_RULE);
        if ($createdByRule === self::class) {
            return null;
        }

        $node->setAttribute(AttributeKey::CREATED_BY_RULE, self::class);
        $args = [new Arg($node->var), new Arg(new String_('getType'))];

        return new Ternary(
            $this->nodeFactory->createFuncCall('method_exists', $args),
            $node,
            $this->nodeFactory->createNull()
        );
    }
}
