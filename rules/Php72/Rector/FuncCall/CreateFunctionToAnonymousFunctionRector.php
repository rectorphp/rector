<?php

declare (strict_types=1);
namespace Rector\Php72\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PhpParser\Node\Scalar\Encapsed;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Expression;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\NodeAnalyzer\ArgsAnalyzer;
use Rector\Core\Php\ReservedKeywordAnalyzer;
use Rector\Core\PhpParser\Parser\InlineCodeParser;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\Php72\NodeFactory\AnonymousFunctionFactory;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://stackoverflow.com/q/48161526/1348344 http://php.net/manual/en/migration72.deprecated.php#migration72.deprecated.create_function-function
 *
 * @see \Rector\Tests\Php72\Rector\FuncCall\CreateFunctionToAnonymousFunctionRector\CreateFunctionToAnonymousFunctionRectorTest
 */
final class CreateFunctionToAnonymousFunctionRector extends \Rector\Core\Rector\AbstractRector implements \Rector\VersionBonding\Contract\MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Parser\InlineCodeParser
     */
    private $inlineCodeParser;
    /**
     * @readonly
     * @var \Rector\Php72\NodeFactory\AnonymousFunctionFactory
     */
    private $anonymousFunctionFactory;
    /**
     * @readonly
     * @var \Rector\Core\Php\ReservedKeywordAnalyzer
     */
    private $reservedKeywordAnalyzer;
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\ArgsAnalyzer
     */
    private $argsAnalyzer;
    public function __construct(\Rector\Core\PhpParser\Parser\InlineCodeParser $inlineCodeParser, \Rector\Php72\NodeFactory\AnonymousFunctionFactory $anonymousFunctionFactory, \Rector\Core\Php\ReservedKeywordAnalyzer $reservedKeywordAnalyzer, \Rector\Core\NodeAnalyzer\ArgsAnalyzer $argsAnalyzer)
    {
        $this->inlineCodeParser = $inlineCodeParser;
        $this->anonymousFunctionFactory = $anonymousFunctionFactory;
        $this->reservedKeywordAnalyzer = $reservedKeywordAnalyzer;
        $this->argsAnalyzer = $argsAnalyzer;
    }
    public function provideMinPhpVersion() : int
    {
        return \Rector\Core\ValueObject\PhpVersionFeature::DEPRECATE_CREATE_FUNCTION;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Use anonymous functions instead of deprecated create_function()', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class ClassWithCreateFunction
{
    public function run()
    {
        $callable = create_function('$matches', "return '$delimiter' . strtolower(\$matches[1]);");
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class ClassWithCreateFunction
{
    public function run()
    {
        $callable = function($matches) use ($delimiter) {
            return $delimiter . strtolower($matches[1]);
        };
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
     * @return Closure|null
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$this->isName($node, 'create_function')) {
            return null;
        }
        if (!$this->argsAnalyzer->isArgsInstanceInArgsPositions($node->args, [0, 1])) {
            return null;
        }
        /** @var Arg $firstArg */
        $firstArg = $node->args[0];
        /** @var Arg $secondArg */
        $secondArg = $node->args[1];
        $params = $this->createParamsFromString($firstArg->value);
        $stmts = $this->parseStringToBody($secondArg->value);
        $refactored = $this->anonymousFunctionFactory->create($params, $stmts, null);
        foreach ($refactored->uses as $key => $use) {
            $variableName = $this->getName($use->var);
            if ($variableName === null) {
                continue;
            }
            if ($this->reservedKeywordAnalyzer->isNativeVariable($variableName)) {
                unset($refactored->uses[$key]);
            }
        }
        return $refactored;
    }
    /**
     * @return Param[]
     */
    private function createParamsFromString(\PhpParser\Node\Expr $expr) : array
    {
        $content = $this->inlineCodeParser->stringify($expr);
        $content = '<?php $value = function(' . $content . ') {};';
        $nodes = $this->inlineCodeParser->parse($content);
        /** @var Expression $expression */
        $expression = $nodes[0];
        /** @var Assign $assign */
        $assign = $expression->expr;
        $function = $assign->expr;
        if (!$function instanceof \PhpParser\Node\Expr\Closure) {
            throw new \Rector\Core\Exception\ShouldNotHappenException();
        }
        return $function->params;
    }
    /**
     * @return Expression[]|Stmt[]
     */
    private function parseStringToBody(\PhpParser\Node\Expr $expr) : array
    {
        if (!$expr instanceof \PhpParser\Node\Scalar\String_ && !$expr instanceof \PhpParser\Node\Scalar\Encapsed && !$expr instanceof \PhpParser\Node\Expr\BinaryOp\Concat) {
            // special case of code elsewhere
            return [$this->createEval($expr)];
        }
        $expr = $this->inlineCodeParser->stringify($expr);
        return $this->inlineCodeParser->parse($expr);
    }
    private function createEval(\PhpParser\Node\Expr $expr) : \PhpParser\Node\Stmt\Expression
    {
        $evalFuncCall = new \PhpParser\Node\Expr\FuncCall(new \PhpParser\Node\Name('eval'), [new \PhpParser\Node\Arg($expr)]);
        return new \PhpParser\Node\Stmt\Expression($evalFuncCall);
    }
}
