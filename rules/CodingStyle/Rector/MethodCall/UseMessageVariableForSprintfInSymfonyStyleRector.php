<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodingStyle\Rector\MethodCall\UseMessageVariableForSprintfInSymfonyStyleRector\UseMessageVariableForSprintfInSymfonyStyleRectorTest
 */
final class UseMessageVariableForSprintfInSymfonyStyleRector extends \Rector\Core\Rector\AbstractRector
{
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Decouple $message property from sprintf() calls in $this->symfonyStyle->method()', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
use Symfony\Component\Console\Style\SymfonyStyle;

final class SomeClass
{
    public function run(SymfonyStyle $symfonyStyle)
    {
        $symfonyStyle->info(sprintf('Hi %s', 'Tom'));
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Component\Console\Style\SymfonyStyle;

final class SomeClass
{
    public function run(SymfonyStyle $symfonyStyle)
    {
        $message = sprintf('Hi %s', 'Tom');
        $symfonyStyle->info($message);
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
        return [\PhpParser\Node\Expr\MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$this->isObjectType($node->var, new \PHPStan\Type\ObjectType('Symfony\\Component\\Console\\Style\\SymfonyStyle'))) {
            return null;
        }
        if (!isset($node->args[0])) {
            return null;
        }
        if (!$node->args[0] instanceof \PhpParser\Node\Arg) {
            return null;
        }
        $argValue = $node->args[0]->value;
        if (!$argValue instanceof \PhpParser\Node\Expr\FuncCall) {
            return null;
        }
        if (!$this->nodeNameResolver->isName($argValue, 'sprintf')) {
            return null;
        }
        $messageVariable = new \PhpParser\Node\Expr\Variable('message');
        $assign = new \PhpParser\Node\Expr\Assign($messageVariable, $argValue);
        $this->nodesToAddCollector->addNodeBeforeNode($assign, $node);
        if (!$node->args[0] instanceof \PhpParser\Node\Arg) {
            return null;
        }
        $node->args[0]->value = $messageVariable;
        return $node;
    }
}
