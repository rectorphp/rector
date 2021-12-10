<?php

declare (strict_types=1);
namespace Rector\Php72\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Expression;
use Rector\Core\PhpParser\Parser\SimplePhpParser;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://github.com/simplesamlphp/simplesamlphp/pull/708/files
 *
 * @see \Rector\Tests\Php72\Rector\FuncCall\StringsAssertNakedRector\StringsAssertNakedRectorTest
 */
final class StringsAssertNakedRector extends \Rector\Core\Rector\AbstractRector implements \Rector\VersionBonding\Contract\MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Parser\SimplePhpParser
     */
    private $simplePhpParser;
    public function __construct(\Rector\Core\PhpParser\Parser\SimplePhpParser $simplePhpParser)
    {
        $this->simplePhpParser = $simplePhpParser;
    }
    public function provideMinPhpVersion() : int
    {
        return \Rector\Core\ValueObject\PhpVersionFeature::STRING_IN_ASSERT_ARG;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('String asserts must be passed directly to assert()', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
function nakedAssert()
{
    assert('true === true');
    assert("true === true");
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
function nakedAssert()
{
    assert(true === true);
    assert(true === true);
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
        if (!$this->isName($node, 'assert')) {
            return null;
        }
        if (!$node->args[0] instanceof \PhpParser\Node\Arg) {
            return null;
        }
        $firstArgValue = $node->args[0]->value;
        if (!$firstArgValue instanceof \PhpParser\Node\Scalar\String_) {
            return null;
        }
        $phpCode = '<?php ' . $firstArgValue->value . ';';
        $contentStmts = $this->simplePhpParser->parseString($phpCode);
        if (!isset($contentStmts[0])) {
            return null;
        }
        if (!$contentStmts[0] instanceof \PhpParser\Node\Stmt\Expression) {
            return null;
        }
        $node->args[0] = new \PhpParser\Node\Arg($contentStmts[0]->expr);
        return $node;
    }
}
