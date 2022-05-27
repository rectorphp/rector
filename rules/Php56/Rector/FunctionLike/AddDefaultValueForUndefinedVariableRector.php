<?php

declare (strict_types=1);
namespace Rector\Php56\Rector\FunctionLike;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Function_;
use Rector\Core\NodeAnalyzer\InlineHTMLAnalyzer;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\Php56\NodeAnalyzer\UndefinedVariableResolver;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://github.com/vimeo/psalm/blob/29b70442b11e3e66113935a2ee22e165a70c74a4/docs/fixing_code.md#possiblyundefinedvariable
 * @changelog https://3v4l.org/MZFel
 *
 * @see \Rector\Tests\Php56\Rector\FunctionLike\AddDefaultValueForUndefinedVariableRector\AddDefaultValueForUndefinedVariableRectorTest
 */
final class AddDefaultValueForUndefinedVariableRector extends \Rector\Core\Rector\AbstractRector implements \Rector\VersionBonding\Contract\MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\Php56\NodeAnalyzer\UndefinedVariableResolver
     */
    private $undefinedVariableResolver;
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\InlineHTMLAnalyzer
     */
    private $inlineHTMLAnalyzer;
    public function __construct(\Rector\Php56\NodeAnalyzer\UndefinedVariableResolver $undefinedVariableResolver, \Rector\Core\NodeAnalyzer\InlineHTMLAnalyzer $inlineHTMLAnalyzer)
    {
        $this->undefinedVariableResolver = $undefinedVariableResolver;
        $this->inlineHTMLAnalyzer = $inlineHTMLAnalyzer;
    }
    public function provideMinPhpVersion() : int
    {
        return \Rector\Core\ValueObject\PhpVersionFeature::REQUIRE_DEFAULT_VALUE;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Adds default value for undefined variable', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        if (rand(0, 1)) {
            $a = 5;
        }
        echo $a;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $a = null;
        if (rand(0, 1)) {
            $a = 5;
        }
        echo $a;
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
        return [\PhpParser\Node\Stmt\ClassMethod::class, \PhpParser\Node\Stmt\Function_::class, \PhpParser\Node\Expr\Closure::class];
    }
    /**
     * @param ClassMethod|Function_|Closure $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($this->inlineHTMLAnalyzer->hasInlineHTML($node)) {
            return null;
        }
        $undefinedVariableNames = $this->undefinedVariableResolver->resolve($node);
        if ($undefinedVariableNames === []) {
            return null;
        }
        $variablesInitiation = [];
        foreach ($undefinedVariableNames as $undefinedVariableName) {
            $value = $this->isArray($undefinedVariableName, (array) $node->stmts) ? new \PhpParser\Node\Expr\Array_([]) : $this->nodeFactory->createNull();
            $assign = new \PhpParser\Node\Expr\Assign(new \PhpParser\Node\Expr\Variable($undefinedVariableName), $value);
            $variablesInitiation[] = new \PhpParser\Node\Stmt\Expression($assign);
        }
        $node->stmts = \array_merge($variablesInitiation, (array) $node->stmts);
        return $node;
    }
    /**
     * @param Stmt[] $stmts
     */
    private function isArray(string $undefinedVariable, array $stmts) : bool
    {
        return (bool) $this->betterNodeFinder->findFirst($stmts, function (\PhpParser\Node $node) use($undefinedVariable) : bool {
            if (!$node instanceof \PhpParser\Node\Expr\ArrayDimFetch) {
                return \false;
            }
            return $this->isName($node->var, $undefinedVariable);
        });
    }
}
