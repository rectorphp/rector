<?php

declare (strict_types=1);
namespace Rector\Symfony\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\AttributeGroup;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
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
        if ($this->phpAttributeAnalyzer->hasPhpAttribute($node, self::ATTRIBUTE)) {
            return null;
        }
        $defaultName = null;
        $property = $node->getProperty('defaultName');
        if ($property instanceof Property) {
            $defaultName = $this->getValueFromProperty($property);
            if ($defaultName !== null) {
                $this->removeNode($property);
            }
        }
        if ($defaultName === null) {
            return null;
        }
        $defaultDescription = null;
        $property = $node->getProperty('defaultDescription');
        if ($property instanceof Property) {
            $defaultDescription = $this->getValueFromProperty($property);
            if ($defaultDescription !== null) {
                $this->removeNode($property);
            }
        }
        $node->attrGroups[] = $this->createAttributeGroup($defaultName, $defaultDescription);
        return $node;
    }
    private function createAttributeGroup(string $defaultName, ?string $defaultDescription) : AttributeGroup
    {
        $attributeGroup = $this->phpAttributeGroupFactory->createFromClass(self::ATTRIBUTE);
        $attributeGroup->attrs[0]->args[] = new Arg(new String_($defaultName));
        if ($defaultDescription !== null) {
            $attributeGroup->attrs[0]->args[] = new Arg(new String_($defaultDescription));
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
}
