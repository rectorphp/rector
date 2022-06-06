<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Php72\Rector\FuncCall;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Arg;
use RectorPrefix20220606\PhpParser\Node\Expr\FuncCall;
use RectorPrefix20220606\PhpParser\Node\Scalar\String_;
use RectorPrefix20220606\PhpParser\Node\Stmt\Expression;
use RectorPrefix20220606\Rector\Core\PhpParser\Parser\SimplePhpParser;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Rector\Core\ValueObject\PhpVersionFeature;
use RectorPrefix20220606\Rector\VersionBonding\Contract\MinPhpVersionInterface;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
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
