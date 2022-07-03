<?php

declare (strict_types=1);
namespace Rector\Laravel\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://laravel.com/docs/8.x/helpers#method-app
 * @changelog https://github.com/laravel/framework/blob/8.x/src/Illuminate/Foundation/helpers.php
 *
 * @see \Rector\Laravel\Tests\Rector\FuncCall\HelperFuncCallToFacadeClassRector\HelperFuncCallToFacadeClassRectorTest
 */
final class HelperFuncCallToFacadeClassRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change app() func calls to facade calls', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        return app('translator')->trans('value');
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        return \Illuminate\Support\Facades\App::get('translator')->trans('value');
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
        return [FuncCall::class];
    }
    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->isName($node->name, 'app')) {
            return null;
        }
        if (\count($node->args) !== 1) {
            return null;
        }
        return $this->nodeFactory->createStaticCall('Illuminate\\Support\\Facades\\App', 'get', $node->args);
    }
}
