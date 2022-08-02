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
final class AddDefaultValueForUndefinedVariableRector extends AbstractRector implements MinPhpVersionInterface
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
    public function __construct(UndefinedVariableResolver $undefinedVariableResolver, InlineHTMLAnalyzer $inlineHTMLAnalyzer)
    {
        $this->undefinedVariableResolver = $undefinedVariableResolver;
        $this->inlineHTMLAnalyzer = $inlineHTMLAnalyzer;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::REQUIRE_DEFAULT_VALUE;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Adds default value for undefined variable', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [ClassMethod::class, Function_::class, Closure::class];
    }
    /**
     * @param ClassMethod|Function_|Closure $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($this->inlineHTMLAnalyzer->hasInlineHTML($node)) {
            return null;
        }
        if ($node->stmts === null) {
            return null;
        }
        $undefinedVariableNames = $this->undefinedVariableResolver->resolve($node);
        if ($undefinedVariableNames === []) {
            return null;
        }
        $variablesInitiation = $this->collectVariablesInitiation($undefinedVariableNames, $node->stmts);
        if ($variablesInitiation === []) {
            return null;
        }
        $node->stmts = \array_merge($variablesInitiation, $node->stmts);
        return $node;
    }
    /**
     * @param Stmt[] $stmts
     * @return Expression[]
     */
    private function collectEarlyExpressionStmts(array $stmts) : array
    {
        $expressionStmts = [];
        foreach ($stmts as $stmt) {
            if (!$stmt instanceof Expression) {
                break;
            }
            $expressionStmts[] = $stmt;
        }
        return $expressionStmts;
    }
    /**
     * @param string[] $undefinedVariableNames
     * @param Stmt[] $stmts
     * @return Expression[]
     */
    private function collectVariablesInitiation(array $undefinedVariableNames, array $stmts) : array
    {
        $variablesInitiation = [];
        $expressionStmts = $this->collectEarlyExpressionStmts($stmts);
        foreach ($undefinedVariableNames as $undefinedVariableName) {
            $value = $this->isArray($undefinedVariableName, $stmts) ? new Array_([]) : $this->nodeFactory->createNull();
            $assign = new Assign(new Variable($undefinedVariableName), $value);
            $expresssion = new Expression($assign);
            foreach ($expressionStmts as $expressionStmt) {
                if ($this->nodeComparator->areNodesEqual($expresssion, $expressionStmt)) {
                    continue 2;
                }
            }
            $variablesInitiation[] = $expresssion;
        }
        return $variablesInitiation;
    }
    /**
     * @param Stmt[] $stmts
     */
    private function isArray(string $undefinedVariable, array $stmts) : bool
    {
        return (bool) $this->betterNodeFinder->findFirst($stmts, function (Node $node) use($undefinedVariable) : bool {
            if (!$node instanceof ArrayDimFetch) {
                return \false;
            }
            return $this->isName($node->var, $undefinedVariable);
        });
    }
}
