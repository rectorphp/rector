<?php

declare(strict_types=1);

namespace Rector\Php72\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Scalar\String_;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\TypeAnalyzer\StringTypeAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see https://3v4l.org/YiTeP
 * @see \Rector\Php72\Tests\Rector\FuncCall\StringifyDefineRector\StringifyDefineRectorTest
 */
final class StringifyDefineRector extends AbstractRector
{
    /**
     * @var StringTypeAnalyzer
     */
    private $stringTypeAnalyzer;

    public function __construct(StringTypeAnalyzer $stringTypeAnalyzer)
    {
        $this->stringTypeAnalyzer = $stringTypeAnalyzer;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Make first argument of define() string', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run(int $a)
    {
         define(CONSTANT_2, 'value');
         define('CONSTANT', 'value');
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run(int $a)
    {
         define('CONSTANT_2', 'value');
         define('CONSTANT', 'value');
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
        if (! $this->isName($node, 'define')) {
            return null;
        }

        if ($this->stringTypeAnalyzer->isStringOrUnionStringOnlyType($node->args[0]->value)) {
            return null;
        }

        if ($node->args[0]->value instanceof ConstFetch) {
            $nodeName = $this->getName($node->args[0]->value);
            if ($nodeName === null) {
                return null;
            }

            $node->args[0]->value = new String_($nodeName);
        }

        return $node;
    }
}
