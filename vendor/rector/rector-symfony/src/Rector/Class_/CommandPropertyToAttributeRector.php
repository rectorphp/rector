<?php

declare (strict_types=1);
namespace Rector\Symfony\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\AttributeGroup;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PhpParser\NodeTraverser;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Php80\NodeAnalyzer\PhpAttributeAnalyzer;
use Rector\PhpAttribute\NodeFactory\PhpAttributeGroupFactory;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://symfony.com/doc/current/console.html#registering-the-command
 *
 * @see \Rector\Symfony\Tests\Rector\Class_\CommandPropertyToAttributeRector\CommandPropertyToAttributeRectorTest
 */
final class CommandPropertyToAttributeRector extends AbstractRector implements MinPhpVersionInterface
{
    private const ATTRIBUTE = 'Symfony\\Component\\Console\\Attribute\\AsCommand';
    /**
     * @readonly
     * @var \Rector\PhpAttribute\NodeFactory\PhpAttributeGroupFactory
     */
    private $phpAttributeGroupFactory;
    /**
     * @readonly
     * @var \Rector\Php80\NodeAnalyzer\PhpAttributeAnalyzer
     */
    private $phpAttributeAnalyzer;
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    public function __construct(PhpAttributeGroupFactory $phpAttributeGroupFactory, PhpAttributeAnalyzer $phpAttributeAnalyzer, ReflectionProvider $reflectionProvider)
    {
        $this->phpAttributeGroupFactory = $phpAttributeGroupFactory;
        $this->phpAttributeAnalyzer = $phpAttributeAnalyzer;
        $this->reflectionProvider = $reflectionProvider;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::ATTRIBUTES;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add Symfony\\Component\\Console\\Attribute\\AsCommand to Symfony Commands and remove the deprecated properties', [new CodeSample(<<<'CODE_SAMPLE'
use Symfony\Component\Console\Command\Command;

final class SunshineCommand extends Command
{
    /** @var string|null */
    public static $defaultName = 'sunshine';
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;

#[AsCommand('sunshine')]
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
        if (!$this->isObjectType($node, new ObjectType('Symfony\\Component\\Console\\Command\\Command'))) {
            return null;
        }
        if (!$this->reflectionProvider->hasClass(self::ATTRIBUTE)) {
            return null;
        }
        $defaultName = $this->resolveDefaultName($node);
        if ($defaultName === null) {
            return null;
        }
        $defaultDescription = $this->resolveDefaultDescription($node);
        $array = $this->resolveAliases($node);
        $constFetch = $this->resolveHidden($node);
        $node->attrGroups[] = $this->createAttributeGroup($defaultName, $defaultDescription, $array, $constFetch);
        return $node;
    }
    private function createAttributeGroup(string $defaultName, ?string $defaultDescription, ?Array_ $array, ?ConstFetch $constFetch) : AttributeGroup
    {
        $attributeGroup = $this->phpAttributeGroupFactory->createFromClass(self::ATTRIBUTE);
        $attributeGroup->attrs[0]->args[] = new Arg(new String_($defaultName));
        if ($defaultDescription !== null) {
            $attributeGroup->attrs[0]->args[] = new Arg(new String_($defaultDescription));
        } elseif ($array !== null || $constFetch !== null) {
            $attributeGroup->attrs[0]->args[] = new Arg($this->nodeFactory->createNull());
        }
        if ($array !== null) {
            $attributeGroup->attrs[0]->args[] = new Arg($array);
        } elseif ($constFetch !== null) {
            $attributeGroup->attrs[0]->args[] = new Arg(new Array_());
        }
        if ($constFetch !== null) {
            $attributeGroup->attrs[0]->args[] = new Arg($constFetch);
        }
        return $attributeGroup;
    }
    private function getValueFromProperty(Property $property) : ?string
    {
        if (\count($property->props) !== 1) {
            return null;
        }
        $propertyProperty = $property->props[0];
        if ($propertyProperty->default instanceof String_) {
            return $propertyProperty->default->value;
        }
        return null;
    }
    private function resolveDefaultName(Class_ $class) : ?string
    {
        $defaultName = null;
        $property = $class->getProperty('defaultName');
        // Get DefaultName from property
        if ($property instanceof Property) {
            $defaultName = $this->getValueFromProperty($property);
            if ($defaultName !== null) {
                $this->removeNode($property);
            }
        }
        // Get DefaultName from attribute
        if ($defaultName === null && $this->phpAttributeAnalyzer->hasPhpAttribute($class, self::ATTRIBUTE)) {
            $defaultName = $this->getDefaultNameFromAttribute($class);
        }
        return $defaultName;
    }
    private function getDefaultNameFromAttribute(Class_ $class) : ?string
    {
        $defaultName = null;
        foreach ($class->attrGroups as $attrGroup) {
            foreach ($attrGroup->attrs as $attribute) {
                if (!$this->nodeNameResolver->isName($attribute->name, self::ATTRIBUTE)) {
                    continue;
                }
                $arg = $attribute->args[0];
                if (!$arg->value instanceof String_) {
                    continue;
                }
                $defaultName = $arg->value->value;
                $this->removeNode($attrGroup);
            }
        }
        return $defaultName;
    }
    private function resolveDefaultDescription(Class_ $class) : ?string
    {
        $defaultDescription = null;
        $property = $class->getProperty('defaultDescription');
        if ($property instanceof Property) {
            $defaultDescription = $this->getValueFromProperty($property);
            if ($defaultDescription !== null) {
                $this->removeNode($property);
            }
        }
        return $defaultDescription;
    }
    private function resolveAliases(Class_ $class) : ?Array_
    {
        $commandAliases = null;
        $classMethod = $class->getMethod('configure');
        if (!$classMethod instanceof ClassMethod) {
            return null;
        }
        $this->traverseNodesWithCallable((array) $classMethod->stmts, function (Node $node) use(&$commandAliases) {
            if (!$node instanceof MethodCall) {
                return null;
            }
            if ($node->isFirstClassCallable()) {
                return null;
            }
            if (!$this->isObjectType($node->var, new ObjectType('Symfony\\Component\\Console\\Command\\Command'))) {
                return null;
            }
            if (!$this->isName($node->name, 'setAliases')) {
                return null;
            }
            /** @var Arg $arg */
            $arg = $node->args[0];
            if (!$arg->value instanceof Array_) {
                return null;
            }
            $commandAliases = $arg->value;
            $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
            if ($parentNode instanceof MethodCall) {
                $parentNode->var = $node->var;
            } else {
                $this->removeNode($node);
            }
            return NodeTraverser::STOP_TRAVERSAL;
        });
        return $commandAliases;
    }
    private function resolveHidden(Class_ $class) : ?ConstFetch
    {
        $commandHidden = null;
        $classMethod = $class->getMethod('configure');
        if (!$classMethod instanceof ClassMethod) {
            return null;
        }
        $this->traverseNodesWithCallable((array) $classMethod->stmts, function (Node $node) use(&$commandHidden) {
            if (!$node instanceof MethodCall) {
                return null;
            }
            if ($node->isFirstClassCallable()) {
                return null;
            }
            if (!$this->isObjectType($node->var, new ObjectType('Symfony\\Component\\Console\\Command\\Command'))) {
                return null;
            }
            if (!$this->isName($node->name, 'setHidden')) {
                return null;
            }
            $commandHidden = $this->getCommandHiddenValue($node);
            $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
            if ($parentNode instanceof MethodCall) {
                $parentNode->var = $node->var;
            } else {
                $this->removeNode($node);
            }
            return NodeTraverser::STOP_TRAVERSAL;
        });
        return $commandHidden;
    }
    private function getCommandHiddenValue(MethodCall $methodCall) : ?ConstFetch
    {
        if (!isset($methodCall->args[0])) {
            return new ConstFetch(new Name('true'));
        }
        /** @var Arg $arg */
        $arg = $methodCall->args[0];
        if (!$arg->value instanceof ConstFetch) {
            return null;
        }
        return $arg->value;
    }
}
