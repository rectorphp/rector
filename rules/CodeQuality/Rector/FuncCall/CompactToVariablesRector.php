<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\MixedType;
use Rector\CodeQuality\CompactConverter;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://stackoverflow.com/a/16319909/1348344
 * @changelog https://3v4l.org/8GJEs
 *
 * @see \Rector\Tests\CodeQuality\Rector\FuncCall\CompactToVariablesRector\CompactToVariablesRectorTest
 */
final class CompactToVariablesRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\CodeQuality\CompactConverter
     */
    private $compactConverter;
    public function __construct(CompactConverter $compactConverter)
    {
        $this->compactConverter = $compactConverter;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change compact() call to own array', [new CodeSample(<<<'CODE_SAMPLE'
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
, <<<'CODE_SAMPLE'
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
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [FuncCall::class];
    }
    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->isName($node, 'compact')) {
            return null;
        }
        if ($this->compactConverter->hasAllArgumentsNamed($node)) {
            return $this->compactConverter->convertToArray($node);
        }
        if ($node->isFirstClassCallable()) {
            return null;
        }
        $firstArg = $node->getArgs()[0];
        $firstValue = $firstArg->value;
        $firstValueStaticType = $this->getType($firstValue);
        if (!$firstValueStaticType instanceof ConstantArrayType) {
            return null;
        }
        if ($firstValueStaticType->getItemType() instanceof MixedType) {
            return null;
        }
        return $this->refactorAssignArray($firstValueStaticType);
    }
    private function refactorAssignArray(ConstantArrayType $constantArrayType) : ?Array_
    {
        $arrayItems = [];
        foreach ($constantArrayType->getValueTypes() as $valueType) {
            if (!$valueType instanceof ConstantStringType) {
                return null;
            }
            $variableName = $valueType->getValue();
            $variable = new Variable($variableName);
            $arrayItems[] = new ArrayItem($variable, new String_($variableName));
        }
        return new Array_($arrayItems);
    }
}
