<?php

declare (strict_types=1);
namespace Rector\Php55\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
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
final class PregReplaceEModifierRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\Php72\NodeFactory\AnonymousFunctionFactory
     */
    private $anonymousFunctionFactory;
    /**
     * @readonly
     * @var \Rector\Php55\RegexMatcher
     */
    private $regexMatcher;
    public function __construct(AnonymousFunctionFactory $anonymousFunctionFactory, RegexMatcher $regexMatcher)
    {
        $this->anonymousFunctionFactory = $anonymousFunctionFactory;
        $this->regexMatcher = $regexMatcher;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::PREG_REPLACE_CALLBACK_E_MODIFIER;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('The /e modifier is no longer supported, use preg_replace_callback instead', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [FuncCall::class];
    }
    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->isName($node, 'preg_replace')) {
            return null;
        }
        if ($node->isFirstClassCallable()) {
            return null;
        }
        if (\count($node->getArgs()) < 2) {
            return null;
        }
        $firstArgument = $node->getArgs()[0];
        $firstArgumentValue = $firstArgument->value;
        $patternWithoutEExpr = $this->regexMatcher->resolvePatternExpressionWithoutEIfFound($firstArgumentValue);
        if ($patternWithoutEExpr === null) {
            return null;
        }
        /** @var Arg $secondArgument */
        $secondArgument = $node->args[1];
        $secondArgumentValue = $secondArgument->value;
        $anonymousFunction = $this->anonymousFunctionFactory->createAnonymousFunctionFromExpr($secondArgumentValue);
        if (!$anonymousFunction instanceof Closure) {
            return null;
        }
        $node->name = new Name('preg_replace_callback');
        $firstArgument->value = $patternWithoutEExpr;
        $secondArgument->value = $anonymousFunction;
        return $node;
    }
}
