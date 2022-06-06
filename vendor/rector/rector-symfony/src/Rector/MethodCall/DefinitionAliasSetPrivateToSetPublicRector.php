<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Symfony\Rector\MethodCall;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr\BooleanNot;
use RectorPrefix20220606\PhpParser\Node\Expr\ConstFetch;
use RectorPrefix20220606\PhpParser\Node\Expr\MethodCall;
use RectorPrefix20220606\PHPStan\Type\ObjectType;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://github.com/symfony/symfony/blob/5.x/UPGRADE-5.2.md#dependencyinjection
 * @see \Rector\Symfony\Tests\Rector\MethodCall\DefinitionAliasSetPrivateToSetPublicRector\DefinitionAliasSetPrivateToSetPublicRectorTest
 */
final class DefinitionAliasSetPrivateToSetPublicRector extends AbstractRector
{
    /**
     * @var ObjectType[]
     */
    private $definitionObjectTypes = [];
    public function __construct()
    {
        $this->definitionObjectTypes = [new ObjectType('Symfony\\Component\\DependencyInjection\\Alias'), new ObjectType('Symfony\\Component\\DependencyInjection\\Definition')];
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Migrates from deprecated Definition/Alias->setPrivate() to Definition/Alias->setPublic()', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->nodeTypeResolver->isObjectTypes($node->var, $this->definitionObjectTypes)) {
            return null;
        }
        if (!$this->isName($node->name, 'setPrivate')) {
            return null;
        }
        $argValue = $node->getArgs()[0]->value;
        $argValue = $argValue instanceof ConstFetch ? $this->createNegationConsFetch($argValue) : new BooleanNot($argValue);
        return $this->nodeFactory->createMethodCall($node->var, 'setPublic', [$argValue]);
    }
    private function createNegationConsFetch(ConstFetch $constFetch) : ConstFetch
    {
        if ($this->valueResolver->isFalse($constFetch)) {
            return $this->nodeFactory->createTrue();
        }
        return $this->nodeFactory->createFalse();
    }
}
