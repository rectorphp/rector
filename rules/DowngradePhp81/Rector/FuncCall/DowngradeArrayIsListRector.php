<?php

declare(strict_types=1);

namespace Rector\DowngradePhp81\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Expression;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpParser\Parser\InlineCodeParser;
use Rector\Core\Rector\AbstractRector;
use Rector\DowngradePhp72\NodeAnalyzer\FunctionExistsFunCallAnalyzer;
use Rector\Naming\Naming\VariableNaming;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @changelog https://wiki.php.net/rfc/is_list
 *
 * @see \Rector\Tests\DowngradePhp81\Rector\FuncCall\DowngradeArrayIsListRector\DowngradeArrayIsListRectorTest
 */
final class DowngradeArrayIsListRector extends AbstractRector
{
    public function __construct(
        private readonly InlineCodeParser $inlineCodeParser,
        private readonly FunctionExistsFunCallAnalyzer $functionExistsFunCallAnalyzer,
        private readonly VariableNaming $variableNaming
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Replace array_is_list() function', [
            new CodeSample(
                <<<'CODE_SAMPLE'
array_is_list([1 => 'apple', 'orange']);
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
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
            ),
        ]);
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [FuncCall::class];
    }

    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node): ?FuncCall
    {
        if ($this->shouldSkip($node)) {
            return null;
        }

        $currentStmt = $node->getAttribute(AttributeKey::CURRENT_STATEMENT);
        if (! $currentStmt instanceof Stmt) {
            return null;
        }

        $scope = $node->getAttribute(AttributeKey::SCOPE);
        $variable = new Variable($this->variableNaming->createCountedValueName('arrayIsList', $scope));

        $function = $this->createClosure();
        $expression = new Expression(new Assign($variable, $function));

        $this->nodesToAddCollector->addNodeBeforeNode($expression, $currentStmt);

        return new FuncCall($variable, $node->args);
    }

    private function createClosure(): Closure
    {
        $stmts = $this->inlineCodeParser->parse(__DIR__ . '/../../snippet/array_is_list_closure.php.inc');

        /** @var Expression $expression */
        $expression = $stmts[0];

        $expr = $expression->expr;
        if (! $expr instanceof Closure) {
            throw new ShouldNotHappenException();
        }

        return $expr;
    }

    private function shouldSkip(FuncCall $funcCall): bool
    {
        if (! $this->nodeNameResolver->isName($funcCall, 'array_is_list')) {
            return true;
        }

        if ($this->functionExistsFunCallAnalyzer->detect($funcCall, 'array_is_list')) {
            return true;
        }

        $args = $funcCall->getArgs();
        return count($args) !== 1;
    }
}
