<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\MixedType;
use Rector\CodeQuality\CompactConverter;
use Rector\CodeQuality\NodeAnalyzer\ArrayCompacter;
use Rector\CodeQuality\NodeAnalyzer\ArrayItemsAnalyzer;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
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

    /**
     * @var ArrayItemsAnalyzer
     */
    private $arrayItemsAnalyzer;

    /**
     * @var ArrayCompacter
     */
    private $arrayCompacter;

    public function __construct(
        CompactConverter $compactConverter,
        ArrayItemsAnalyzer $arrayItemsAnalyzer,
        ArrayCompacter $arrayCompacter
    ) {
        $this->compactConverter = $compactConverter;
        $this->arrayItemsAnalyzer = $arrayItemsAnalyzer;
        $this->arrayCompacter = $arrayCompacter;
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

        if ($firstValueStaticType instanceof ConstantArrayType && ! $firstValueStaticType->getItemType() instanceof MixedType) {
            return $this->refactorAssignArray($firstValue, $node);
        }

        return null;
    }

    private function refactorAssignedArray(Assign $assign, FuncCall $funcCall, Expr $expr): ?Expr
    {
        if (! $assign->expr instanceof Array_) {
            return null;
        }

        $array = $assign->expr;

        $assignScope = $assign->getAttribute(AttributeKey::SCOPE);
        if (! $assignScope instanceof Scope) {
            return null;
        }

        $isCompactOfUndefinedVariables = $this->arrayItemsAnalyzer->hasArrayExclusiveDefinedVariableNames(
            $array,
            $assignScope
        );
        if ($isCompactOfUndefinedVariables) {
            $funcCallScope = $funcCall->getAttribute(AttributeKey::SCOPE);
            if (! $funcCallScope instanceof Scope) {
                return null;
            }

            $isCompactOfDefinedVariables = $this->arrayItemsAnalyzer->hasArrayExclusiveUndefinedVariableNames(
                $array,
                $funcCallScope
            );
            if ($isCompactOfDefinedVariables) {
                $this->arrayCompacter->compactStringToVariableArray($array);
                return $expr;
            }
        }

        $this->removeNode($assign);

        $this->arrayCompacter->compactStringToVariableArray($array);

        $assignVariable = $funcCall->args[0]->value;
        $preAssign = new Assign($assignVariable, $array);

        $currentStatement = $funcCall->getAttribute(AttributeKey::CURRENT_STATEMENT);
        $this->addNodeBeforeNode($preAssign, $currentStatement);

        return $expr;
    }

    private function refactorAssignArray(Expr $expr, FuncCall $funcCall): ?Expr
    {
        $previousAssign = $this->betterNodeFinder->findPreviousAssignToExpr($expr);
        if (! $previousAssign instanceof Assign) {
            return null;
        }

        return $this->refactorAssignedArray($previousAssign, $funcCall, $expr);
    }
}
