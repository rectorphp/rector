<?php

declare (strict_types=1);
namespace Rector\Php55\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use Rector\Core\NodeAnalyzer\ArgsAnalyzer;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\Php55\RegexMatcher;
use Rector\Php72\NodeFactory\AnonymousFunctionFactory;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/remove_preg_replace_eval_modifier https://stackoverflow.com/q/19245205/1348344
 *
 * @see \Rector\Tests\Php55\Rector\FuncCall\PregReplaceEModifierRector\PregReplaceEModifierRectorTest
 */
final class PregReplaceEModifierRector extends \Rector\Core\Rector\AbstractRector implements \Rector\VersionBonding\Contract\MinPhpVersionInterface
{
    /**
     * @var \Rector\Php72\NodeFactory\AnonymousFunctionFactory
     */
    private $anonymousFunctionFactory;
    /**
     * @var \Rector\Php55\RegexMatcher
     */
    private $regexMatcher;
    /**
     * @var \Rector\Core\NodeAnalyzer\ArgsAnalyzer
     */
    private $argsAnalyzer;
    public function __construct(\Rector\Php72\NodeFactory\AnonymousFunctionFactory $anonymousFunctionFactory, \Rector\Php55\RegexMatcher $regexMatcher, \Rector\Core\NodeAnalyzer\ArgsAnalyzer $argsAnalyzer)
    {
        $this->anonymousFunctionFactory = $anonymousFunctionFactory;
        $this->regexMatcher = $regexMatcher;
        $this->argsAnalyzer = $argsAnalyzer;
    }
    public function provideMinPhpVersion() : int
    {
        return \Rector\Core\ValueObject\PhpVersionFeature::PREG_REPLACE_CALLBACK_E_MODIFIER;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('The /e modifier is no longer supported, use preg_replace_callback instead', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $comment = preg_replace('~\b(\w)(\w+)~e', '"$1".strtolower("$2")', $comment);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $comment = preg_replace_callback('~\b(\w)(\w+)~', function ($matches) {
              return($matches[1].strtolower($matches[2]));
        }, $comment);
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
        if (!$this->isName($node, 'preg_replace')) {
            return null;
        }
        if (!$this->argsAnalyzer->isArgsInstanceInArgsPositions($node->args, [0, 1])) {
            return null;
        }
        /** @var Arg $firstArgument */
        $firstArgument = $node->args[0];
        $firstArgumentValue = $firstArgument->value;
        $patternWithoutE = $this->regexMatcher->resolvePatternExpressionWithoutEIfFound($firstArgumentValue);
        if (!$patternWithoutE instanceof \PhpParser\Node\Expr) {
            return null;
        }
        /** @var Arg $secondArgument */
        $secondArgument = $node->args[1];
        $secondArgumentValue = $secondArgument->value;
        $anonymousFunction = $this->anonymousFunctionFactory->createAnonymousFunctionFromString($secondArgumentValue);
        if (!$anonymousFunction instanceof \PhpParser\Node\Expr\Closure) {
            return null;
        }
        $node->name = new \PhpParser\Node\Name('preg_replace_callback');
        $firstArgument->value = $patternWithoutE;
        $secondArgument->value = $anonymousFunction;
        return $node;
    }
}
