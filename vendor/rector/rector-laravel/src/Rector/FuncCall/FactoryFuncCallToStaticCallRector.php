<?php

declare (strict_types=1);
namespace Rector\Laravel\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\StaticCall;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://laravel.com/docs/7.x/database-testing#creating-models
 * @see https://laravel.com/docs/8.x/database-testing#instantiating-models
 *
 * @see \Rector\Laravel\Tests\Rector\FuncCall\FactoryFuncCallToStaticCallRector\FactoryFuncCallToStaticCallRectorTest
 */
final class FactoryFuncCallToStaticCallRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var string
     */
    private const FACTORY = 'factory';
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Use the static factory method instead of global factory function.', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
factory(User::class);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
User::factory();
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
     * @param Node\Expr\FuncCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$this->isName($node->name, self::FACTORY)) {
            return null;
        }
        if (!isset($node->args[0])) {
            return null;
        }
        if (!$node->args[0] instanceof \PhpParser\Node\Arg) {
            return null;
        }
        $firstArgValue = $node->args[0]->value;
        if (!$firstArgValue instanceof \PhpParser\Node\Expr\ClassConstFetch) {
            return null;
        }
        $model = $firstArgValue->class;
        // create model
        if (!isset($node->args[1])) {
            return new \PhpParser\Node\Expr\StaticCall($model, self::FACTORY);
        }
        // create models of a given type
        return new \PhpParser\Node\Expr\StaticCall($model, self::FACTORY, [$node->args[1]]);
    }
}
