<?php

declare (strict_types=1);
namespace Rector\Symfony\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://github.com/symfony/symfony/blob/5.x/UPGRADE-5.2.md#dependencyinjection
 * @see \Rector\Symfony\Tests\Rector\MethodCall\DefinitionAliasSetPrivateToSetPublicRector\DefinitionAliasSetPrivateToSetPublicRectorTest
 */
final class DefinitionAliasSetPrivateToSetPublicRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var ObjectType[]
     */
    private $definitionObjectTypes = [];
    public function __construct()
    {
        $this->definitionObjectTypes = [new \PHPStan\Type\ObjectType('Symfony\\Component\\DependencyInjection\\Alias'), new \PHPStan\Type\ObjectType('Symfony\\Component\\DependencyInjection\\Definition')];
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Migrates from deprecated Definition/Alias->setPrivate() to Definition/Alias->setPublic()', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\Definition;

class SomeClass
{
    public function run()
    {
        $definition = new Definition('Example\Foo');
        $definition->setPrivate(false);

        $alias = new Alias('Example\Foo');
        $alias->setPrivate(false);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\Definition;

class SomeClass
{
    public function run()
    {
        $definition = new Definition('Example\Foo');
        $definition->setPublic(true);

        $alias = new Alias('Example\Foo');
        $alias->setPublic(true);
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
        if (!$this->nodeTypeResolver->isObjectTypes($node->var, $this->definitionObjectTypes)) {
            return null;
        }
        if (!$this->isName($node->name, 'setPrivate')) {
            return null;
        }
        $argValue = $node->getArgs()[0]->value;
        $argValue = $argValue instanceof \PhpParser\Node\Expr\ConstFetch ? $this->createNegationConsFetch($argValue) : new \PhpParser\Node\Expr\BooleanNot($argValue);
        return $this->nodeFactory->createMethodCall($node->var, 'setPublic', [$argValue]);
    }
    private function createNegationConsFetch(\PhpParser\Node\Expr\ConstFetch $constFetch) : \PhpParser\Node\Expr\ConstFetch
    {
        if ($this->valueResolver->isFalse($constFetch)) {
            return $this->nodeFactory->createTrue();
        }
        return $this->nodeFactory->createFalse();
    }
}
