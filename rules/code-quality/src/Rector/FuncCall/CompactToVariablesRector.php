<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;
use PHPStan\Type\Constant\ConstantArrayType;
use Rector\CodeQuality\CompactConverter;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see https://stackoverflow.com/a/16319909/1348344
 * @see https://3v4l.org/8GJEs
 * @see \Rector\CodeQuality\Tests\Rector\FuncCall\CompactToVariablesRector\CompactToVariablesRectorTest
 */
final class CompactToVariablesRector extends AbstractRector
{
    /**
     * @var CompactConverter
     */
    private $compactConverter;

    public function __construct(CompactConverter $compactConverter)
    {
        $this->compactConverter = $compactConverter;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change compact() call to own array', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $checkout = 'one';
        $form = 'two';

        return compact('checkout', 'form');
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $checkout = 'one';
        $form = 'two';

        return ['checkout' => $checkout, 'form' => $form];
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
        return [FuncCall::class];
    }

    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isName($node, 'compact')) {
            return null;
        }

        if ($this->compactConverter->hasAllArgumentsNamed($node)) {
            return $this->compactConverter->convertToArray($node);
        }

        $firstValue = $node->args[0]->value;
        $firstValueStaticType = $this->getStaticType($firstValue);

        if ($firstValueStaticType instanceof ConstantArrayType) {
            return $this->refactorAssignArray($firstValue);
        }

        return null;
    }

    private function refactorAssignedArray(Array_ $array): void
    {
        foreach ($array->items as $arrayItem) {
            if (! $arrayItem instanceof ArrayItem) {
                continue;
            }

            if ($arrayItem->key !== null) {
                continue;
            }

            if (! $arrayItem->value instanceof String_) {
                continue;
            }

            $arrayItem->key = $arrayItem->value;
            $arrayItem->value = new Variable($arrayItem->value->value);
        }
    }

    private function refactorAssignArray(Expr $expr): ?Expr
    {
        $previousAssign = $this->betterNodeFinder->findPreviousAssignToExpr($expr);
        if (! $previousAssign instanceof Assign) {
            return null;
        }

        if (! $previousAssign->expr instanceof Array_) {
            return null;
        }

        $this->refactorAssignedArray($previousAssign->expr);

        return $expr;
    }
}
