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
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/is_list
 *
 * @see \Rector\Tests\DowngradePhp81\Rector\FuncCall\DowngradeArrayIsListRector\DowngradeArrayIsListRectorTest
 */
final class DowngradeArrayIsListRector extends \Rector\Core\Rector\AbstractScopeAwareRector
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
    public function __construct(\Rector\Core\PhpParser\Parser\InlineCodeParser $inlineCodeParser, \Rector\DowngradePhp72\NodeAnalyzer\FunctionExistsFunCallAnalyzer $functionExistsFunCallAnalyzer, \Rector\Naming\Naming\VariableNaming $variableNaming)
    {
        $this->inlineCodeParser = $inlineCodeParser;
        $this->functionExistsFunCallAnalyzer = $functionExistsFunCallAnalyzer;
        $this->variableNaming = $variableNaming;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Replace array_is_list() function', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
array_is_list([1 => 'apple', 'orange']);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$arrayIsList = function (array $array) : bool {
    if (function_exists('array_is_list')) {
        return array_is_list($array);
    }
    if ($array === []) {
        return true;
    }
    $current_key = 0;
    foreach ($array as $key => $noop) {
        if ($key !== $current_key) {
            return false;
        }
        ++$current_key;
    }
    return true;
};
$arrayIsList([1 => 'apple', 'orange']);
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
    public function refactorWithScope(\PhpParser\Node $node, \PHPStan\Analyser\Scope $scope) : ?\PhpParser\Node\Expr\FuncCall
    {
        if ($this->shouldSkip($node)) {
            return null;
        }
        $variable = new \PhpParser\Node\Expr\Variable($this->variableNaming->createCountedValueName('arrayIsList', $scope));
        $function = $this->createClosure();
        $expression = new \PhpParser\Node\Stmt\Expression(new \PhpParser\Node\Expr\Assign($variable, $function));
        $this->nodesToAddCollector->addNodeBeforeNode($expression, $node, $this->file->getSmartFileInfo());
        return new \PhpParser\Node\Expr\FuncCall($variable, $node->args);
    }
    private function createClosure() : \PhpParser\Node\Expr\Closure
    {
        if ($this->cachedClosure instanceof \PhpParser\Node\Expr\Closure) {
            return clone $this->cachedClosure;
        }
        $stmts = $this->inlineCodeParser->parse(__DIR__ . '/../../snippet/array_is_list_closure.php.inc');
        /** @var Expression $expression */
        $expression = $stmts[0];
        $expr = $expression->expr;
        if (!$expr instanceof \PhpParser\Node\Expr\Closure) {
            throw new \Rector\Core\Exception\ShouldNotHappenException();
        }
        $this->cachedClosure = $expr;
        return $expr;
    }
    private function shouldSkip(\PhpParser\Node\Expr\FuncCall $funcCall) : bool
    {
        if (!$this->nodeNameResolver->isName($funcCall, 'array_is_list')) {
            return \true;
        }
        if ($this->functionExistsFunCallAnalyzer->detect($funcCall, 'array_is_list')) {
            return \true;
        }
        $args = $funcCall->getArgs();
        return \count($args) !== 1;
    }
}
