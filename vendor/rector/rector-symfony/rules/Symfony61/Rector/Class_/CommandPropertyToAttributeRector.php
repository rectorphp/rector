<?php

declare (strict_types=1);
namespace Rector\Symfony\Symfony61\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Attribute;
use PhpParser\Node\AttributeGroup;
use PhpParser\Node\Expr;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\ObjectType;
use Rector\PhpAttribute\NodeFactory\PhpAttributeGroupFactory;
use Rector\Rector\AbstractRector;
use Rector\Symfony\Enum\SymfonyAnnotation;
use Rector\Symfony\Enum\SymfonyClass;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://symfony.com/doc/current/console.html#registering-the-command
 *
 * @see \Rector\Symfony\Tests\Symfony61\Rector\Class_\CommandPropertyToAttributeRector\CommandPropertyToAttributeRectorTest
 */
final class CommandPropertyToAttributeRector extends AbstractRector implements MinPhpVersionInterface
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
        return new RuleDefinition('Add Symfony\\Component\\Console\\Attribute\\AsCommand to Symfony Commands and remove the deprecated properties', [new CodeSample(<<<'CODE_SAMPLE'
use Symfony\Component\Console\Command\Command;

final class SunshineCommand extends Command
{
    public static $defaultName = 'sunshine';

    public static $defaultDescription = 'Ssome description';
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;

#[AsCommand(name: 'sunshine', description: 'some description')]
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
        if (!$this->isObjectType($node, new ObjectType(SymfonyClass::COMMAND))) {
            return null;
        }
        // does attribute already exist?
        if (!$this->reflectionProvider->hasClass(SymfonyAnnotation::AS_COMMAND)) {
            return null;
        }
        $defaultNameExpr = $this->resolvePropertyExpr($node, 'defaultName');
        if (!$defaultNameExpr instanceof Expr) {
            return null;
        }
        $defaultDescriptionExpr = $this->resolvePropertyExpr($node, 'defaultDescription');
        return $this->replaceAsCommandAttribute($node, $this->createAttributeGroupAsCommand($defaultNameExpr, $defaultDescriptionExpr));
    }
    private function createAttributeGroupAsCommand(Expr $defaultNameExpr, ?Expr $defaultDescription) : AttributeGroup
    {
        // name is always required to add
        $nameArg = $this->createNamedArg('name', $defaultNameExpr);
        $attributeGroup = $this->phpAttributeGroupFactory->createFromClass(SymfonyAnnotation::AS_COMMAND);
        $attributeGroup->attrs[0]->args[] = $nameArg;
        if ($defaultDescription instanceof Expr) {
            $descriptionArg = $this->createNamedArg('description', $defaultDescription);
            $attributeGroup->attrs[0]->args[] = $descriptionArg;
        }
        return $attributeGroup;
    }
    private function resolvePropertyExpr(Class_ $class, string $propertyName) : ?Expr
    {
        foreach ($class->stmts as $key => $stmt) {
            if (!$stmt instanceof Property) {
                continue;
            }
            if (!$this->isName($stmt, $propertyName)) {
                continue;
            }
            $defaultExpr = $stmt->props[0]->default;
            if ($defaultExpr instanceof Expr) {
                // remove property
                unset($class->stmts[$key]);
                return $defaultExpr;
            }
        }
        return null;
    }
    private function replaceAsCommandAttribute(Class_ $class, AttributeGroup $createAttributeGroup) : ?Class_
    {
        $hasAsCommandAttribute = \false;
        $replacedAsCommandAttribute = \false;
        foreach ($class->attrGroups as $attrGroup) {
            foreach ($attrGroup->attrs as $attribute) {
                if ($this->nodeNameResolver->isName($attribute->name, SymfonyAnnotation::AS_COMMAND)) {
                    $hasAsCommandAttribute = \true;
                    $replacedAsCommandAttribute = $this->replaceArguments($attribute, $createAttributeGroup);
                }
            }
        }
        if ($hasAsCommandAttribute === \false) {
            $class->attrGroups[] = $createAttributeGroup;
            $replacedAsCommandAttribute = \true;
        }
        if ($replacedAsCommandAttribute === \false) {
            return null;
        }
        return $class;
    }
    private function replaceArguments(Attribute $attribute, AttributeGroup $createAttributeGroup) : bool
    {
        $replacedAsCommandAttribute = \false;
        if (!$attribute->args[0]->value instanceof String_) {
            $attribute->args[0] = $createAttributeGroup->attrs[0]->args[0];
            $replacedAsCommandAttribute = \true;
        }
        if (!isset($attribute->args[1]) && isset($createAttributeGroup->attrs[0]->args[1])) {
            $attribute->args[1] = $createAttributeGroup->attrs[0]->args[1];
            $replacedAsCommandAttribute = \true;
        }
        if (!isset($attribute->args[2]) && isset($createAttributeGroup->attrs[0]->args[2])) {
            $attribute->args[2] = $createAttributeGroup->attrs[0]->args[2];
            $replacedAsCommandAttribute = \true;
        }
        if (!isset($attribute->args[3]) && isset($createAttributeGroup->attrs[0]->args[3])) {
            $attribute->args[3] = $createAttributeGroup->attrs[0]->args[3];
            $replacedAsCommandAttribute = \true;
        }
        return $replacedAsCommandAttribute;
    }
    private function createNamedArg(string $name, Expr $expr) : Arg
    {
        return new Arg($expr, \false, \false, [], new Identifier($name));
    }
}
