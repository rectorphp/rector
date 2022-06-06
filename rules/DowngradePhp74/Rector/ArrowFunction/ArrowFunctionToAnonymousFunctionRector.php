<?php

declare (strict_types=1);
namespace Rector\DowngradePhp74\Rector\ArrowFunction;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\Rector\AbstractRector;
use Rector\Php72\NodeFactory\AnonymousFunctionFactory;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://www.php.net/manual/en/functions.arrow.php
 *
 * @see \Rector\Tests\DowngradePhp74\Rector\ArrowFunction\ArrowFunctionToAnonymousFunctionRector\ArrowFunctionToAnonymousFunctionRectorTest
 */
final class ArrowFunctionToAnonymousFunctionRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Php72\NodeFactory\AnonymousFunctionFactory
     */
    private $anonymousFunctionFactory;
    public function __construct(\Rector\Php72\NodeFactory\AnonymousFunctionFactory $anonymousFunctionFactory)
    {
        $this->anonymousFunctionFactory = $anonymousFunctionFactory;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Replace arrow functions with anonymous functions', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $delimiter = ",";
        $callable = fn($matches) => $delimiter . strtolower($matches[1]);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $delimiter = ",";
        $callable = function ($matches) use ($delimiter) {
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
        return [\PhpParser\Node\Expr\ArrowFunction::class];
    }
    /**
     * @param ArrowFunction $node
     */
    public function refactor(\PhpParser\Node $node) : \PhpParser\Node\Expr\Closure
    {
        $stmts = [new \PhpParser\Node\Stmt\Return_($node->expr)];
        return $this->anonymousFunctionFactory->create($node->params, $stmts, $node->returnType, $node->static);
    }
}
