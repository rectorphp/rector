<?php

declare (strict_types=1);
namespace Rector\Laravel\Rector\StaticCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://laravel.com/docs/5.7/upgrade
 * @see \Rector\Laravel\Tests\Rector\StaticCall\Redirect301ToPermanentRedirectRector\Redirect301ToPermanentRedirectRectorTest
 */
final class Redirect301ToPermanentRedirectRector extends AbstractRector
{
    /**
     * @var ObjectType[]
     */
    private $routerObjectTypes = [];
    public function __construct()
    {
        $this->routerObjectTypes = [new ObjectType('Illuminate\\Support\\Facades\\Route'), new ObjectType('Illuminate\\Routing\\Route')];
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change "redirect" call with 301 to "permanentRedirect"', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        Illuminate\Routing\Route::redirect('/foo', '/bar', 301);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        Illuminate\Routing\Route::permanentRedirect('/foo', '/bar');
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
        return [StaticCall::class];
    }
    /**
     * @param StaticCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->nodeTypeResolver->isObjectTypes($node->class, $this->routerObjectTypes)) {
            return null;
        }
        if (!isset($node->args[2])) {
            return null;
        }
        if (!$node->args[2] instanceof Arg) {
            return null;
        }
        $is301 = $this->valueResolver->isValue($node->args[2]->value, 301);
        if (!$is301) {
            return null;
        }
        unset($node->args[2]);
        $node->name = new Identifier('permanentRedirect');
        return $node;
    }
}
