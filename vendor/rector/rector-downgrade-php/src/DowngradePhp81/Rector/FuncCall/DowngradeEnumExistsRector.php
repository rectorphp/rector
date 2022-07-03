<?php

declare (strict_types=1);
namespace Rector\DowngradePhp81\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Expression;
use PHPStan\Analyser\Scope;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpParser\Parser\InlineCodeParser;
use Rector\Core\Rector\AbstractScopeAwareRector;
use Rector\DowngradePhp72\NodeAnalyzer\FunctionExistsFunCallAnalyzer;
use Rector\Naming\Naming\VariableNaming;
use Rector\PostRector\Collector\NodesToAddCollector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://php.watch/versions/8.1/enums#enum-exists
 *
 * @see \Rector\Tests\DowngradePhp81\Rector\FuncCall\DowngradeEnumExistsRector\DowngradeEnumExistsRectorTest
 * @see https://github.com/symfony/polyfill/blob/d45b58e9f527e408fb28c4b6431b7284f8c168ff/src/Php81/bootstrap.php#L27
 */
final class DowngradeEnumExistsRector extends AbstractScopeAwareRector
{
    /**
     * @var \PhpParser\Node\Expr\Closure|null
     */
    private $cachedClosure;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Parser\InlineCodeParser
     */
    private $inlineCodeParser;
    /**
     * @readonly
     * @var \Rector\DowngradePhp72\NodeAnalyzer\FunctionExistsFunCallAnalyzer
     */
    private $functionExistsFunCallAnalyzer;
    /**
     * @readonly
     * @var \Rector\Naming\Naming\VariableNaming
     */
    private $variableNaming;
    /**
     * @readonly
     * @var \Rector\PostRector\Collector\NodesToAddCollector
     */
    private $nodesToAddCollector;
    public function __construct(InlineCodeParser $inlineCodeParser, FunctionExistsFunCallAnalyzer $functionExistsFunCallAnalyzer, VariableNaming $variableNaming, NodesToAddCollector $nodesToAddCollector)
    {
        $this->inlineCodeParser = $inlineCodeParser;
        $this->functionExistsFunCallAnalyzer = $functionExistsFunCallAnalyzer;
        $this->variableNaming = $variableNaming;
        $this->nodesToAddCollector = $nodesToAddCollector;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Replace enum_exists() function', [new CodeSample(<<<'CODE_SAMPLE'
enum_exists('SomeEnum', true);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$enumExists = function (string $enum, bool $autoload = true) : bool {
    if (function_exists('enum_exists')) {
        return enum_exists($enum, $autoload);
    }
    return $autoload && class_exists($enum) && false;
};
$enumExists('SomeEnum', true);
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
    public function refactorWithScope(Node $node, Scope $scope) : ?FuncCall
    {
        if ($this->shouldSkip($node)) {
            return null;
        }
        $variable = new Variable($this->variableNaming->createCountedValueName('enumExists', $scope));
        $function = $this->createClosure();
        $expression = new Expression(new Assign($variable, $function));
        $this->nodesToAddCollector->addNodeBeforeNode($expression, $node);
        return new FuncCall($variable, $node->args);
    }
    private function createClosure() : Closure
    {
        if ($this->cachedClosure instanceof Closure) {
            return clone $this->cachedClosure;
        }
        $stmts = $this->inlineCodeParser->parse(__DIR__ . '/../../snippet/enum_exists_closure.php.inc');
        /** @var Expression $expression */
        $expression = $stmts[0];
        $expr = $expression->expr;
        if (!$expr instanceof Closure) {
            throw new ShouldNotHappenException();
        }
        $this->cachedClosure = $expr;
        return $expr;
    }
    private function shouldSkip(FuncCall $funcCall) : bool
    {
        if (!$this->nodeNameResolver->isName($funcCall, 'enum_exists')) {
            return \true;
        }
        if ($this->functionExistsFunCallAnalyzer->detect($funcCall, 'enum_exists')) {
            return \true;
        }
        $args = $funcCall->getArgs();
        return \count($args) < 1;
    }
}
