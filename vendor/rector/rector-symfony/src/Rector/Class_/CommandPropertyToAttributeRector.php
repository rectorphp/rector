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
final class CommandPropertyToAttributeRector extends \Rector\Core\Rector\AbstractRector implements \Rector\VersionBonding\Contract\MinPhpVersionInterface
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
    public function __construct(\Rector\PhpAttribute\NodeFactory\PhpAttributeGroupFactory $phpAttributeGroupFactory, \Rector\Php80\NodeAnalyzer\PhpAttributeAnalyzer $phpAttributeAnalyzer, \PHPStan\Reflection\ReflectionProvider $reflectionProvider)
    {
        $this->phpAttributeGroupFactory = $phpAttributeGroupFactory;
        $this->phpAttributeAnalyzer = $phpAttributeAnalyzer;
        $this->reflectionProvider = $reflectionProvider;
    }
    public function provideMinPhpVersion() : int
    {
        return \Rector\Core\ValueObject\PhpVersionFeature::ATTRIBUTES;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Add Symfony\\Component\\Console\\Attribute\\AsCommand to Symfony Commands and remove the deprecated properties', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SunshineCommand extends \Symfony\Component\Console\Command\Command
{
    /** @var string|null */
    public static $defaultName = 'sunshine';
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
#[\Symfony\Component\Console\Attribute\AsCommand('sunshine')]
class SunshineCommand extends \Symfony\Component\Console\Command\Command
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
        return [\PhpParser\Node\Stmt\Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$this->isObjectType($node, new \PHPStan\Type\ObjectType('Symfony\\Component\\Console\\Command\\Command'))) {
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
        if ($property instanceof \PhpParser\Node\Stmt\Property) {
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
        if ($property instanceof \PhpParser\Node\Stmt\Property) {
            $defaultDescription = $this->getValueFromProperty($property);
            if ($defaultDescription !== null) {
                $this->removeNode($property);
            }
        }
        $node->attrGroups[] = $this->createAttributeGroup($defaultName, $defaultDescription);
        return $node;
    }
    private function createAttributeGroup(string $defaultName, ?string $defaultDescription) : \PhpParser\Node\AttributeGroup
    {
        $attributeGroup = $this->phpAttributeGroupFactory->createFromClass(self::ATTRIBUTE);
        $attributeGroup->attrs[0]->args[] = new \PhpParser\Node\Arg(new \PhpParser\Node\Scalar\String_($defaultName));
        if ($defaultDescription !== null) {
            $attributeGroup->attrs[0]->args[] = new \PhpParser\Node\Arg(new \PhpParser\Node\Scalar\String_($defaultDescription));
        }
        return $attributeGroup;
    }
    private function getValueFromProperty(\PhpParser\Node\Stmt\Property $property) : ?string
    {
        if (\count($property->props) !== 1) {
            return null;
        }
        $propertyProperty = $property->props[0];
        if ($propertyProperty->default instanceof \PhpParser\Node\Scalar\String_) {
            return $propertyProperty->default->value;
        }
        return null;
    }
}
