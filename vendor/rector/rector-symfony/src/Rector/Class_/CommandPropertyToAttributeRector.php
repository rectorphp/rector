<?php

declare (strict_types=1);
namespace Rector\Symfony\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Attribute;
use PhpParser\Node\AttributeGroup;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\Php80\NodeAnalyzer\PhpAttributeAnalyzer;
use Rector\PhpAttribute\NodeFactory\PhpAttributeGroupFactory;
use Rector\Symfony\Helper\CommandHelper;
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
    /**
     * @readonly
     * @var \Rector\Symfony\Helper\CommandHelper
     */
    private $commandHelper;
    public function __construct(PhpAttributeGroupFactory $phpAttributeGroupFactory, PhpAttributeAnalyzer $phpAttributeAnalyzer, ReflectionProvider $reflectionProvider, CommandHelper $commandHelper)
    {
        $this->phpAttributeGroupFactory = $phpAttributeGroupFactory;
        $this->phpAttributeAnalyzer = $phpAttributeAnalyzer;
        $this->reflectionProvider = $reflectionProvider;
        $this->commandHelper = $commandHelper;
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
        if (!$this->reflectionProvider->hasClass(CommandHelper::ATTRIBUTE)) {
            return null;
        }
        $defaultName = $this->resolveDefaultName($node);
        if ($defaultName === null) {
            return null;
        }
        $defaultDescription = $this->resolveDefaultDescription($node);
        $array = $this->commandHelper->getCommandAliasesValueFromAttributeOrSetter($node);
        $constFetch = $this->commandHelper->getCommandHiddenValueFromAttributeOrSetter($node);
        return $this->replaceAsCommandAttribute($node, $this->createAttributeGroupAsCommand($defaultName, $defaultDescription, $array, $constFetch));
    }
    private function createAttributeGroupAsCommand(string $defaultName, ?string $defaultDescription, ?Array_ $array, ?ConstFetch $constFetch) : AttributeGroup
    {
        $attributeGroup = $this->phpAttributeGroupFactory->createFromClass(CommandHelper::ATTRIBUTE);
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
        if ($defaultName === null && $this->phpAttributeAnalyzer->hasPhpAttribute($class, CommandHelper::ATTRIBUTE)) {
            $defaultNameFromArgument = $this->commandHelper->getArgumentValueFromAttribute($class, 0);
            if (\is_string($defaultNameFromArgument)) {
                $defaultName = $defaultNameFromArgument;
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
        return $this->resolveDefaultDescriptionFromAttribute($class, $defaultDescription);
    }
    private function resolveDefaultDescriptionFromAttribute(Class_ $class, ?string $defaultDescription) : ?string
    {
        if ($defaultDescription === null && $this->phpAttributeAnalyzer->hasPhpAttribute($class, CommandHelper::ATTRIBUTE)) {
            $defaultDescriptionFromArgument = $this->commandHelper->getArgumentValueFromAttribute($class, 1);
            if (\is_string($defaultDescriptionFromArgument)) {
                $defaultDescription = $defaultDescriptionFromArgument;
            }
        }
        return $defaultDescription;
    }
    private function replaceAsCommandAttribute(Class_ $class, AttributeGroup $createAttributeGroup) : ?Class_
    {
        $hasAsCommandAttribute = \false;
        $replacedAsCommandAttribute = \false;
        foreach ($class->attrGroups as $attrGroup) {
            foreach ($attrGroup->attrs as $attribute) {
                if ($this->nodeNameResolver->isName($attribute->name, CommandHelper::ATTRIBUTE)) {
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
}
