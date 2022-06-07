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
final class StringsAssertNakedRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Parser\SimplePhpParser
     */
    private $simplePhpParser;
    public function __construct(SimplePhpParser $simplePhpParser)
    {
        $this->simplePhpParser = $simplePhpParser;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::STRING_IN_ASSERT_ARG;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('String asserts must be passed directly to assert()', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [FuncCall::class];
    }
    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->isName($node, 'assert')) {
            return null;
        }
        if (!$node->args[0] instanceof Arg) {
            return null;
        }
        $firstArgValue = $node->args[0]->value;
        if (!$firstArgValue instanceof String_) {
            return null;
        }
        $phpCode = '<?php ' . $firstArgValue->value . ';';
        $contentStmts = $this->simplePhpParser->parseString($phpCode);
        if (!isset($contentStmts[0])) {
            return null;
        }
        if (!$contentStmts[0] instanceof Expression) {
            return null;
        }
        $node->args[0] = new Arg($contentStmts[0]->expr);
        return $node;
    }
}
