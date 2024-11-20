<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Scalar\String_;
use Rector\NodeTypeResolver\TypeAnalyzer\StringTypeAnalyzer;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodingStyle\Rector\FuncCall\ConsistentImplodeRector\ConsistentImplodeRectorTest
 */
final class ConsistentImplodeRector extends AbstractRector
{
    /**
     * @readonly
     */
    private StringTypeAnalyzer $stringTypeAnalyzer;
    public function __construct(StringTypeAnalyzer $stringTypeAnalyzer)
    {
        $this->stringTypeAnalyzer = $stringTypeAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Changes various implode forms to consistent one', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run(array $items)
    {
        $itemsAsStrings = implode($items);
        $itemsAsStrings = implode($items, '|');
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run(array $items)
    {
        $itemsAsStrings = implode('', $items);
        $itemsAsStrings = implode('|', $items);
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
        if (!$this->isNames($node, ['implode', 'join'])) {
            return null;
        }
        if ($node->isFirstClassCallable()) {
            return null;
        }
        if (\count($node->getArgs()) === 1) {
            // complete default value ''
            $node->args[1] = $node->getArgs()[0];
            $node->args[0] = new Arg(new String_(''));
            return $node;
        }
        $firstArg = $node->getArgs()[0];
        $firstArgumentValue = $firstArg->value;
        $firstArgumentType = $this->getType($firstArgumentValue);
        if ($firstArgumentType->isString()->yes()) {
            return null;
        }
        if (\count($node->getArgs()) !== 2) {
            return null;
        }
        $secondArg = $node->getArgs()[1];
        if ($this->stringTypeAnalyzer->isStringOrUnionStringOnlyType($secondArg->value)) {
            $node->args = \array_reverse($node->getArgs());
            return $node;
        }
        return null;
    }
}
