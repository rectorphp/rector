<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Php56\Rector\FunctionLike;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr\Array_;
use RectorPrefix20220606\PhpParser\Node\Expr\ArrayDimFetch;
use RectorPrefix20220606\PhpParser\Node\Expr\Assign;
use RectorPrefix20220606\PhpParser\Node\Expr\Closure;
use RectorPrefix20220606\PhpParser\Node\Expr\Variable;
use RectorPrefix20220606\PhpParser\Node\Stmt;
use RectorPrefix20220606\PhpParser\Node\Stmt\ClassMethod;
use RectorPrefix20220606\PhpParser\Node\Stmt\Expression;
use RectorPrefix20220606\PhpParser\Node\Stmt\Function_;
use RectorPrefix20220606\Rector\Core\NodeAnalyzer\InlineHTMLAnalyzer;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Rector\Core\ValueObject\PhpVersionFeature;
use RectorPrefix20220606\Rector\Php56\NodeAnalyzer\UndefinedVariableResolver;
use RectorPrefix20220606\Rector\VersionBonding\Contract\MinPhpVersionInterface;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
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
        $undefinedVariableNames = $this->undefinedVariableResolver->resolve($node);
        if ($undefinedVariableNames === []) {
            return null;
        }
        $variablesInitiation = [];
        foreach ($undefinedVariableNames as $undefinedVariableName) {
            $value = $this->isArray($undefinedVariableName, (array) $node->stmts) ? new Array_([]) : $this->nodeFactory->createNull();
            $assign = new Assign(new Variable($undefinedVariableName), $value);
            $variablesInitiation[] = new Expression($assign);
        }
        $node->stmts = \array_merge($variablesInitiation, (array) $node->stmts);
        return $node;
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
