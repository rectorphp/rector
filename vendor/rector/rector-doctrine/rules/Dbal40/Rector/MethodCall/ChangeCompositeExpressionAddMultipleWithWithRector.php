<?php

declare (strict_types=1);
namespace Rector\Doctrine\Dbal40\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://github.com/doctrine/dbal/blob/4.0.x/UPGRADE.md#bc-break-removed-compositeexpression-methods
 * @see \Rector\Doctrine\Tests\Dbal40\Rector\MethodCall\ChangeCompositeExpressionAddMultipleWithWithRector\ChangeCompositeExpressionAddMultipleWithWithRectorTest
 */
final class ChangeCompositeExpressionAddMultipleWithWithRector extends AbstractRector
{
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [MethodCall::class];
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change CompositeExpression ->addMultiple($parts) to ->with(...$parts)', [new CodeSample(<<<'CODE_SAMPLE'
use Doctrine\ORM\EntityRepository;
use Doctrine\DBAL\Query\Expression\CompositeExpression;

class SomeRepository extends EntityRepository
{
    public function getSomething($parts)
    {
        $compositeExpression = CompositeExpression::and('', ...$parts);
        $compositeExpression->addMultiple($parts);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Doctrine\ORM\EntityRepository;
use Doctrine\DBAL\Query\Expression\CompositeExpression;

class SomeRepository extends EntityRepository
{
    public function getSomething($parts)
    {
        $compositeExpression = CompositeExpression::and('', ...$parts);
        $compositeExpression->with(...$parts);
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->isName($node->name, 'addMultiple')) {
            return null;
        }
        if (!$this->nodeTypeResolver->isObjectType($node->var, new ObjectType('Doctrine\\DBAL\\Query\\Expression\\CompositeExpression'))) {
            return null;
        }
        if ($node->isFirstClassCallable()) {
            return null;
        }
        $node->name = new Identifier('with');
        $firstArg = $node->getArgs()[0];
        $firstArg->value = new ArrayItem($firstArg->value, null, \false, [], \true);
        return $node;
    }
}
