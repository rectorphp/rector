<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Scalar\String_;
use Rector\Core\NodeAnalyzer\ArgsAnalyzer;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\TypeAnalyzer\StringTypeAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog http://php.net/manual/en/function.implode.php#refsect1-function.implode-description
 * @see https://3v4l.org/iYTgh
 * @see \Rector\Tests\CodingStyle\Rector\FuncCall\ConsistentImplodeRector\ConsistentImplodeRectorTest
 */
final class ConsistentImplodeRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\TypeAnalyzer\StringTypeAnalyzer
     */
    private $stringTypeAnalyzer;
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\ArgsAnalyzer
     */
    private $argsAnalyzer;
    public function __construct(\Rector\NodeTypeResolver\TypeAnalyzer\StringTypeAnalyzer $stringTypeAnalyzer, \Rector\Core\NodeAnalyzer\ArgsAnalyzer $argsAnalyzer)
    {
        $this->stringTypeAnalyzer = $stringTypeAnalyzer;
        $this->argsAnalyzer = $argsAnalyzer;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Changes various implode forms to consistent one', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run(array $items)
    {
        $itemsAsStrings = implode($items);
        $itemsAsStrings = implode($items, '|');

        $itemsAsStrings = implode('|', $items);
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
        return [\PhpParser\Node\Expr\FuncCall::class];
    }
    /**
     * @param FuncCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$this->isName($node, 'implode')) {
            return null;
        }
        if (\count($node->args) === 1) {
            // complete default value ''
            $node->args[1] = $node->args[0];
            $node->args[0] = new \PhpParser\Node\Arg(new \PhpParser\Node\Scalar\String_(''));
            return $node;
        }
        if (!$this->argsAnalyzer->isArgInstanceInArgsPosition($node->args, 0)) {
            return null;
        }
        /** @var Arg $arg0 */
        $arg0 = $node->args[0];
        $firstArgumentValue = $arg0->value;
        if ($firstArgumentValue instanceof \PhpParser\Node\Scalar\String_) {
            return null;
        }
        if (\count($node->args) !== 2) {
            return null;
        }
        if (!$this->argsAnalyzer->isArgInstanceInArgsPosition($node->args, 1)) {
            return null;
        }
        /** @var Arg $arg1 */
        $arg1 = $node->args[1];
        if ($this->stringTypeAnalyzer->isStringOrUnionStringOnlyType($arg1->value)) {
            $node->args = \array_reverse($node->args);
            return $node;
        }
        return null;
    }
}
