<?php

declare (strict_types=1);
namespace Rector\Laravel\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Scalar\String_;
use Rector\Core\Rector\AbstractRector;
use Rector\Laravel\NodeAnalyzer\LumenRouteRegisteringMethodAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Laravel\Tests\Rector\MethodCall\LumenRoutesStringActionToUsesArrayRector\LumenRoutesStringActionToUsesArrayRectorTest
 */
final class LumenRoutesStringActionToUsesArrayRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Laravel\NodeAnalyzer\LumenRouteRegisteringMethodAnalyzer
     */
    private $lumenRouteRegisteringMethodAnalyzer;
    public function __construct(LumenRouteRegisteringMethodAnalyzer $lumenRouteRegisteringMethodAnalyzer)
    {
        $this->lumenRouteRegisteringMethodAnalyzer = $lumenRouteRegisteringMethodAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Changes action in rule definitions from string to array notation.', [new CodeSample(<<<'CODE_SAMPLE'
$router->get('/user', 'UserController@get');
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$router->get('/user', ['uses => 'UserController@get']);
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [MethodCall::class];
    }
    public function refactor(Node $node) : ?Node
    {
        if (!$node instanceof MethodCall) {
            return null;
        }
        if (!$this->lumenRouteRegisteringMethodAnalyzer->isLumenRoutingClass($node)) {
            return null;
        }
        if (!$this->lumenRouteRegisteringMethodAnalyzer->isRoutesRegisterRoute($node->name)) {
            return null;
        }
        $string = $node->getArgs()[1]->value;
        if (!$string instanceof String_) {
            return null;
        }
        $node->args[1] = new Arg(new Array_([new ArrayItem($string, new String_('uses'))]));
        return $node;
    }
}
