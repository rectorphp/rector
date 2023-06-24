<?php

declare (strict_types=1);
namespace Rector\Symfony\Symfony61\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Attribute;
use PhpParser\Node\AttributeGroup;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\Php80\NodeAnalyzer\PhpAttributeAnalyzer;
use Rector\PhpAttribute\NodeFactory\PhpAttributeGroupFactory;
use Rector\Symfony\Enum\SymfonyAnnotation;
use Rector\Symfony\NodeAnalyzer\Command\AttributeValueResolver;
use Rector\Symfony\NodeAnalyzer\Command\SetAliasesMethodCallExtractor;
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
     * @var \Rector\Php80\NodeAnalyzer\PhpAttributeAnalyzer
     */
    private $phpAttributeAnalyzer;
    /**
     * @readonly
     * @var \Rector\Symfony\NodeAnalyzer\Command\AttributeValueResolver
     */
    private $attributeValueResolver;
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    /**
     * @readonly
     * @var \Rector\Symfony\NodeAnalyzer\Command\SetAliasesMethodCallExtractor
     */
    private $setAliasesMethodCallExtractor;
    public function __construct(PhpAttributeGroupFactory $phpAttributeGroupFactory, PhpAttributeAnalyzer $phpAttributeAnalyzer, AttributeValueResolver $attributeValueResolver, ReflectionProvider $reflectionProvider, SetAliasesMethodCallExtractor $setAliasesMethodCallExtractor)
    {
        $this->phpAttributeGroupFactory = $phpAttributeGroupFactory;
        $this->phpAttributeAnalyzer = $phpAttributeAnalyzer;
        $this->attributeValueResolver = $attributeValueResolver;
        $this->reflectionProvider = $reflectionProvider;
        $this->setAliasesMethodCallExtractor = $setAliasesMethodCallExtractor;
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
        if (!$this->reflectionProvider->hasClass(SymfonyAnnotation::AS_COMMAND)) {
            return null;
        }
        $defaultName = $this->resolveDefaultName($node);
        if ($defaultName === null) {
            return null;
        }
        $defaultDescription = $this->resolveDefaultDescription($node);
        $alisesArray = $this->setAliasesMethodCallExtractor->resolveCommandAliasesFromAttributeOrSetter($node);
        return $this->replaceAsCommandAttribute($node, $this->createAttributeGroupAsCommand($defaultName, $defaultDescription, $alisesArray));
    }
    private function createAttributeGroupAsCommand(string $defaultName, ?string $defaultDescription, ?Array_ $aliasesArray) : AttributeGroup
    {
        $attributeGroup = $this->phpAttributeGroupFactory->createFromClass(SymfonyAnnotation::AS_COMMAND);
        $attributeGroup->attrs[0]->args[] = new Arg(new String_($defaultName));
        if ($defaultDescription !== null) {
            $attributeGroup->attrs[0]->args[] = new Arg(new String_($defaultDescription));
        } elseif ($aliasesArray instanceof Array_) {
            $attributeGroup->attrs[0]->args[] = new Arg($this->nodeFactory->createNull());
        }
        if ($aliasesArray instanceof Array_) {
            $attributeGroup->attrs[0]->args[] = new Arg($aliasesArray);
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
        foreach ($class->stmts as $key => $stmt) {
            if (!$stmt instanceof Property) {
                continue;
            }
            if (!$this->isName($stmt->props[0], 'defaultName')) {
                continue;
            }
            $defaultName = $this->getValueFromProperty($stmt);
            if ($defaultName !== null) {
                // remove property
                unset($class->stmts[$key]);
                return $defaultName;
            }
        }
        return $this->defaultDefaultNameFromAttribute($class);
    }
    private function resolveDefaultDescription(Class_ $class) : ?string
    {
        foreach ($class->stmts as $key => $stmt) {
            if (!$stmt instanceof Property) {
                continue;
            }
            if (!$this->isName($stmt, 'defaultDescription')) {
                continue;
            }
            $defaultDescription = $this->getValueFromProperty($stmt);
            if ($defaultDescription !== null) {
                unset($class->stmts[$key]);
                return $defaultDescription;
            }
        }
        return $this->resolveDefaultDescriptionFromAttribute($class);
    }
    private function resolveDefaultDescriptionFromAttribute(Class_ $class) : ?string
    {
        if ($this->phpAttributeAnalyzer->hasPhpAttribute($class, SymfonyAnnotation::AS_COMMAND)) {
            $defaultDescriptionFromArgument = $this->attributeValueResolver->getArgumentValueFromAttribute($class, 1);
            if (\is_string($defaultDescriptionFromArgument)) {
                return $defaultDescriptionFromArgument;
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
    private function defaultDefaultNameFromAttribute(Class_ $class) : ?string
    {
        if (!$this->phpAttributeAnalyzer->hasPhpAttribute($class, SymfonyAnnotation::AS_COMMAND)) {
            return null;
        }
        $defaultNameFromArgument = $this->attributeValueResolver->getArgumentValueFromAttribute($class, 0);
        if (\is_string($defaultNameFromArgument)) {
            return $defaultNameFromArgument;
        }
        return null;
    }
}
