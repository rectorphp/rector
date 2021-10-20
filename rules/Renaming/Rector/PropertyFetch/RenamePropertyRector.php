<?php

declare (strict_types=1);
namespace Rector\Renaming\Rector\PropertyFetch;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\VarLikeIdentifier;
use PHPStan\Type\ThisType;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Renaming\ValueObject\RenameProperty;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix20211020\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Renaming\Rector\PropertyFetch\RenamePropertyRector\RenamePropertyRectorTest
 */
final class RenamePropertyRector extends \Rector\Core\Rector\AbstractRector implements \Rector\Core\Contract\Rector\ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const RENAMED_PROPERTIES = 'old_to_new_property_by_types';
    /**
     * @var RenameProperty[]
     */
    private $renamedProperties = [];
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Replaces defined old properties by new ones.', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample('$someObject->someOldProperty;', '$someObject->someNewProperty;', [self::RENAMED_PROPERTIES => [new \Rector\Renaming\ValueObject\RenameProperty('SomeClass', 'someOldProperty', 'someNewProperty')]])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\PropertyFetch::class, \PhpParser\Node\Stmt\ClassLike::class];
    }
    /**
     * @param PropertyFetch|ClassLike $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($node instanceof \PhpParser\Node\Stmt\ClassLike) {
            return $this->processFromClassLike($node);
        }
        return $this->processFromPropertyFetch($node);
    }
    /**
     * @param array<string, RenameProperty[]> $configuration
     */
    public function configure(array $configuration) : void
    {
        $renamedProperties = $configuration[self::RENAMED_PROPERTIES] ?? [];
        \RectorPrefix20211020\Webmozart\Assert\Assert::allIsInstanceOf($renamedProperties, \Rector\Renaming\ValueObject\RenameProperty::class);
        $this->renamedProperties = $renamedProperties;
    }
    private function processFromClassLike(\PhpParser\Node\Stmt\ClassLike $classLike) : \PhpParser\Node\Stmt\ClassLike
    {
        foreach ($this->renamedProperties as $renamedProperty) {
            $this->renameProperty($classLike, $renamedProperty);
        }
        return $classLike;
    }
    private function renameProperty(\PhpParser\Node\Stmt\ClassLike $classLike, \Rector\Renaming\ValueObject\RenameProperty $renameProperty) : void
    {
        $classLikeName = $this->nodeNameResolver->getName($classLike);
        $renamePropertyObjectType = $renameProperty->getObjectType();
        $className = $renamePropertyObjectType->getClassName();
        if ($classLikeName !== $className) {
            return;
        }
        $property = $classLike->getProperty($renameProperty->getOldProperty());
        if (!$property instanceof \PhpParser\Node\Stmt\Property) {
            return;
        }
        $newProperty = $renameProperty->getNewProperty();
        $targetNewProperty = $classLike->getProperty($newProperty);
        if ($targetNewProperty instanceof \PhpParser\Node\Stmt\Property) {
            return;
        }
        $property->props[0]->name = new \PhpParser\Node\VarLikeIdentifier($newProperty);
    }
    private function processFromPropertyFetch(\PhpParser\Node\Expr\PropertyFetch $propertyFetch) : ?\PhpParser\Node\Expr\PropertyFetch
    {
        $class = $propertyFetch->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CLASS_NODE);
        foreach ($this->renamedProperties as $renamedProperty) {
            if (!$this->isObjectType($propertyFetch->var, $renamedProperty->getObjectType())) {
                continue;
            }
            $oldProperty = $renamedProperty->getOldProperty();
            if (!$this->isName($propertyFetch, $oldProperty)) {
                continue;
            }
            $nodeVarType = $this->nodeTypeResolver->getType($propertyFetch->var);
            if ($nodeVarType instanceof \PHPStan\Type\ThisType && $class instanceof \PhpParser\Node\Stmt\ClassLike) {
                $this->renameProperty($class, $renamedProperty);
            }
            $propertyFetch->name = new \PhpParser\Node\Identifier($renamedProperty->getNewProperty());
            return $propertyFetch;
        }
        return null;
    }
}
