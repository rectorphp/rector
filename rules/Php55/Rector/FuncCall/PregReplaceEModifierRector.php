<?php

declare (strict_types=1);
namespace Rector\Php55\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use Rector\Core\Rector\AbstractRector;
use Rector\Php55\NodeFactory\AnonymousFunctionNodeFactory;
use Rector\Php55\RegexMatcher;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/remove_preg_replace_eval_modifier https://stackoverflow.com/q/19245205/1348344
 *
 * @see \Rector\Tests\Php55\Rector\FuncCall\PregReplaceEModifierRector\PregReplaceEModifierRectorTest
 */
final class PregReplaceEModifierRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var RegexMatcher
     */
    private $regexMatcher;
    /**
     * @var AnonymousFunctionNodeFactory
     */
    private $anonymousFunctionNodeFactory;
    public function __construct(\Rector\Php55\NodeFactory\AnonymousFunctionNodeFactory $anonymousFunctionNodeFactory, \Rector\Php55\RegexMatcher $regexMatcher)
    {
        $this->regexMatcher = $regexMatcher;
        $this->anonymousFunctionNodeFactory = $anonymousFunctionNodeFactory;
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
        }, , $comment);
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
        $firstArgumentValue = $node->args[0]->value;
        $patternWithoutE = $this->regexMatcher->resolvePatternExpressionWithoutEIfFound($firstArgumentValue);
        if (!$patternWithoutE instanceof \PhpParser\Node\Expr) {
            return null;
        }
        $secondArgumentValue = $node->args[1]->value;
        $anonymousFunction = $this->anonymousFunctionNodeFactory->createAnonymousFunctionFromString($secondArgumentValue);
        if (!$anonymousFunction instanceof \PhpParser\Node\Expr\Closure) {
            return null;
        }
        $node->name = new \PhpParser\Node\Name('preg_replace_callback');
        $node->args[0]->value = $patternWithoutE;
        $node->args[1]->value = $anonymousFunction;
        return $node;
    }
}
