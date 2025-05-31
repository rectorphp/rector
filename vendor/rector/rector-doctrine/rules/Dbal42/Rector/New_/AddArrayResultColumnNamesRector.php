<?php

declare (strict_types=1);
namespace Rector\Doctrine\Dbal42\Rector\New_;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\BinaryOp\Coalesce;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\LNumber;
use Rector\Doctrine\Enum\DoctrineClass;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://github.com/doctrine/dbal/pull/6504/files
 *
 * @see \Dbal40\Rector\MethodCall\New_\AddArrayResultColumnNamesRector\AddArrayResultColumnNamesRectorTest
 */
final class AddArrayResultColumnNamesRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add column names argument to ArrayResult object', [new CodeSample(<<<'CODE_SAMPLE'
use Doctrine\DBAL\Cache\ArrayResult;

final class SomeClass
{
    public function create(array $items)
    {
        $result = new ArrayResult($items);

        return $result;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Doctrine\DBAL\Cache\ArrayResult;

final class SomeClass
{
    public function create(array $items)
    {
        $result = new ArrayResult(array_keys($items[0] ?? []), $items);

        return $result;
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [New_::class];
    }
    /**
     * @param New_ $node
     */
    public function refactor(Node $node) : ?New_
    {
        if ($node->isFirstClassCallable()) {
            return null;
        }
        if (!$this->isName($node->class, DoctrineClass::ARRAY_RESULT)) {
            return null;
        }
        if (\count($node->getArgs()) !== 1) {
            return null;
        }
        $itemsExpr = $node->getArgs()[0]->value;
        // pass column names as 1st argument
        $arrayDimFetch = new ArrayDimFetch($itemsExpr, new LNumber(0));
        $arrayCoalesce = new Coalesce($arrayDimFetch, new Array_());
        $arrayKeysFuncCall = new FuncCall(new Name('array_keys'), [new Arg($arrayCoalesce)]);
        $node->args[0] = new Arg($arrayKeysFuncCall);
        $node->args[] = new Arg($itemsExpr);
        return $node;
    }
}
