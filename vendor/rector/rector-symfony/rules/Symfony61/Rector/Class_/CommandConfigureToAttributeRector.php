<?php

declare (strict_types=1);
namespace Rector\Symfony\Symfony61\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Attribute;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\ObjectType;
use Rector\PhpAttribute\NodeFactory\PhpAttributeGroupFactory;
use Rector\Rector\AbstractRector;
use Rector\Symfony\Enum\SymfonyAnnotation;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://symfony.com/doc/current/console.html#registering-the-command
 *
 * @see \Rector\Symfony\Tests\Symfony61\Rector\Class_\CommandConfigureToAttributeRector\CommandConfigureToAttributeRectorTest
 */
final class CommandConfigureToAttributeRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\PhpAttribute\NodeFactory\PhpAttributeGroupFactory
     */
    private $phpAttributeGroupFactory;
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    /**
     * @var array<string, string>
     */
    private const METHODS_TO_ATTRIBUTE_NAMES = ['setName' => 'name', 'setDescription' => 'description', 'setAliases' => 'aliases', 'setHidden' => 'hidden'];
    public function __construct(PhpAttributeGroupFactory $phpAttributeGroupFactory, ReflectionProvider $reflectionProvider)
    {
        $this->phpAttributeGroupFactory = $phpAttributeGroupFactory;
        $this->reflectionProvider = $reflectionProvider;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::ATTRIBUTES;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add Symfony\\Component\\Console\\Attribute\\AsCommand to Symfony Commands from configure()', [new CodeSample(<<<'CODE_SAMPLE'
use Symfony\Component\Console\Command\Command;

final class SunshineCommand extends Command
{
    public function configure()
    {
        $this->setName('sunshine');
        $this->setDescription('Some description');

    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;

#[AsCommand(name: 'sunshine', description: 'Some description')]
final class SunshineCommand extends Command
{
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($node->isAbstract()) {
            return null;
        }
        if (!$this->reflectionProvider->hasClass(SymfonyAnnotation::AS_COMMAND)) {
            return null;
        }
        if (!$this->isObjectType($node, new ObjectType('Symfony\\Component\\Console\\Command\\Command'))) {
            return null;
        }
        $configureClassMethod = $node->getMethod('configure');
        if (!$configureClassMethod instanceof ClassMethod) {
            return null;
        }
        // handle existing attribute
        $asCommandAttribute = null;
        $attributeArgs = [];
        foreach ($node->attrGroups as $attrGroup) {
            foreach ($attrGroup->attrs as $attribute) {
                if (!$this->nodeNameResolver->isName($attribute->name, SymfonyAnnotation::AS_COMMAND)) {
                    continue;
                }
                $asCommandAttribute = $attribute;
                foreach ($asCommandAttribute->args as $arg) {
                    if ($arg->name === null) {
                        // when the existing attribute does not use named arguments, we cannot upgrade
                        return null;
                    }
                    $attributeArgs[] = $arg;
                }
                break 2;
            }
        }
        if (!$asCommandAttribute instanceof Attribute) {
            $asCommandAttributeGroup = $this->phpAttributeGroupFactory->createFromClass(SymfonyAnnotation::AS_COMMAND);
            $asCommandAttribute = $asCommandAttributeGroup->attrs[0];
            $node->attrGroups[] = $asCommandAttributeGroup;
        }
        foreach (self::METHODS_TO_ATTRIBUTE_NAMES as $methodName => $attributeName) {
            $resolvedExpr = $this->findAndRemoveMethodExpr($configureClassMethod, $methodName);
            if ($resolvedExpr instanceof Expr) {
                $attributeArgs[] = $this->createNamedArg($attributeName, $resolvedExpr);
            }
        }
        $asCommandAttribute->args = $attributeArgs;
        // remove left overs
        foreach ((array) $configureClassMethod->stmts as $key => $stmt) {
            if ($this->isExpressionVariableThis($stmt)) {
                unset($configureClassMethod->stmts[$key]);
            }
        }
        return $node;
    }
    private function createNamedArg(string $name, Expr $expr) : Arg
    {
        return new Arg($expr, \false, \false, [], new Identifier($name));
    }
    private function findAndRemoveMethodExpr(ClassMethod $classMethod, string $methodName) : ?Expr
    {
        $expr = null;
        $this->traverseNodesWithCallable((array) $classMethod->stmts, function (Node $node) use(&$expr, $methodName) {
            // find setName() method call
            if (!$node instanceof MethodCall) {
                return null;
            }
            if (!$this->isName($node->name, $methodName)) {
                return null;
            }
            $expr = $node->getArgs()[0]->value;
            return $node->var;
        });
        return $expr;
    }
    private function isExpressionVariableThis(Stmt $stmt) : bool
    {
        if (!$stmt instanceof Expression) {
            return \false;
        }
        if (!$stmt->expr instanceof Variable) {
            return \false;
        }
        $variable = $stmt->expr;
        return $this->isName($variable, 'this');
    }
}
