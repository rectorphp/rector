<?php

declare (strict_types=1);
namespace Rector\Nette\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://github.com/nette/di/pull/146/files
 *
 * @see \Rector\Nette\Tests\Rector\MethodCall\SetClassWithArgumentToSetFactoryRector\SetClassWithArgumentToSetFactoryRectorTest
 */
final class SetClassWithArgumentToSetFactoryRector extends \Rector\Core\Rector\AbstractRector
{
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Change setClass with class and arguments to separated methods', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
use Nette\DI\ContainerBuilder;

class SomeClass
{
    public function run(ContainerBuilder $containerBuilder)
    {
        $containerBuilder->addDefinition('...')
            ->setClass('SomeClass', [1, 2]);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Nette\DI\ContainerBuilder;

class SomeClass
{
    public function run(ContainerBuilder $containerBuilder)
    {
        $containerBuilder->addDefinition('...')
            ->setFactory('SomeClass', [1, 2]);
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
        if (!$this->isName($node->name, 'setClass')) {
            return null;
        }
        if (\count($node->args) !== 2) {
            return null;
        }
        if (!$this->isObjectType($node->var, new \PHPStan\Type\ObjectType('Nette\\DI\\Definitions\\ServiceDefinition'))) {
            return null;
        }
        $node->name = new \PhpParser\Node\Identifier('setFactory');
        return $node;
    }
}
