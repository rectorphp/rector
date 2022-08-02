<?php

declare (strict_types=1);
namespace Rector\Laravel\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\VariadicPlaceholder;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix202208\Webmozart\Assert\Assert;
/**
 * @changelog https://laravel.com/docs/7.x/database-testing#creating-models
 * @changelog https://laravel.com/docs/8.x/database-testing#applying-states
 *
 * @see \Rector\Laravel\Tests\Rector\MethodCall\FactoryApplyingStatesRector\FactoryApplyingStatesRectorTest
 */
final class FactoryApplyingStatesRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Call the state methods directly instead of specify the name of state.', [new CodeSample(<<<'CODE_SAMPLE'
$factory->state('delinquent');
$factory->states('premium', 'delinquent');
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$factory->delinquent();
$factory->premium()->delinquent();
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Expr>>
     */
    public function getNodeTypes() : array
    {
        return [MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->isNames($node->name, ['state', 'states'])) {
            return null;
        }
        if (!$this->isObjectType($node->var, new ObjectType('Illuminate\\Database\\Eloquent\\FactoryBuilder'))) {
            return null;
        }
        $var = $node->var;
        $states = $this->getStatesFromArgs($node->args);
        Assert::allString($states);
        foreach ($states as $state) {
            $var = $this->nodeFactory->createMethodCall($var, $state);
        }
        return $var;
    }
    /**
     * @param array<Arg|VariadicPlaceholder> $args
     * @return mixed[]
     */
    private function getStatesFromArgs(array $args) : array
    {
        if (\count($args) === 1 && isset($args[0]) && $args[0] instanceof Arg) {
            return (array) $this->valueResolver->getValue($args[0]->value);
        }
        return \array_map(function ($arg) {
            return $arg instanceof Arg ? $this->valueResolver->getValue($arg->value) : null;
        }, $args);
    }
}
