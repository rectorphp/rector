<?php

declare (strict_types=1);
namespace Rector\Php72\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Scalar\String_;
use Rector\NodeTypeResolver\TypeAnalyzer\StringTypeAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Php72\Rector\FuncCall\StringifyDefineRector\StringifyDefineRectorTest
 */
final class StringifyDefineRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\TypeAnalyzer\StringTypeAnalyzer
     */
    private $stringTypeAnalyzer;
    public function __construct(StringTypeAnalyzer $stringTypeAnalyzer)
    {
        $this->stringTypeAnalyzer = $stringTypeAnalyzer;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::STRING_IN_FIRST_DEFINE_ARG;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Make first argument of define() string', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run(int $a)
    {
         define(CONSTANT_2, 'value');
         define('CONSTANT', 'value');
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run(int $a)
    {
         define('CONSTANT_2', 'value');
         define('CONSTANT', 'value');
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
        if (!$this->isName($node, 'define')) {
            return null;
        }
        if ($node->isFirstClassCallable()) {
            return null;
        }
        if (!isset($node->getArgs()[0])) {
            return null;
        }
        $firstArg = $node->getArgs()[0];
        if ($this->stringTypeAnalyzer->isStringOrUnionStringOnlyType($firstArg->value)) {
            return null;
        }
        if ($firstArg->value instanceof ConstFetch) {
            $nodeName = $this->getName($firstArg->value);
            if ($nodeName === null) {
                return null;
            }
            $firstArg->value = new String_($nodeName);
        }
        return $node;
    }
}
