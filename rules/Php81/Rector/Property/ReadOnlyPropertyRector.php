<?php

declare (strict_types=1);
namespace Rector\Php81\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Property;
use Rector\Core\NodeAnalyzer\ParamAnalyzer;
use Rector\Core\NodeManipulator\PropertyFetchAssignManipulator;
use Rector\Core\NodeManipulator\PropertyManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\Core\ValueObject\Visibility;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Privatization\NodeManipulator\VisibilityManipulator;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/readonly_properties_v2
 *
 * @see \Rector\Tests\Php81\Rector\Property\ReadOnlyPropertyRector\ReadOnlyPropertyRectorTest
 */
final class ReadOnlyPropertyRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\Core\NodeManipulator\PropertyManipulator
     */
    private $propertyManipulator;
    /**
     * @readonly
     * @var \Rector\Core\NodeManipulator\PropertyFetchAssignManipulator
     */
    private $propertyFetchAssignManipulator;
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\ParamAnalyzer
     */
    private $paramAnalyzer;
    /**
     * @readonly
     * @var \Rector\Privatization\NodeManipulator\VisibilityManipulator
     */
    private $visibilityManipulator;
    public function __construct(PropertyManipulator $propertyManipulator, PropertyFetchAssignManipulator $propertyFetchAssignManipulator, ParamAnalyzer $paramAnalyzer, VisibilityManipulator $visibilityManipulator)
    {
        $this->propertyManipulator = $propertyManipulator;
        $this->propertyFetchAssignManipulator = $propertyFetchAssignManipulator;
        $this->paramAnalyzer = $paramAnalyzer;
        $this->visibilityManipulator = $visibilityManipulator;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Decorate read-only property with `readonly` attribute', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function __construct(
        private string $name
    ) {
    }

    public function getName()
    {
        return $this->name;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function __construct(
        private readonly string $name
    ) {
    }

    public function getName()
    {
        return $this->name;
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
        return [Property::class, Param::class];
    }
    /**
     * @param Property|Param $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($node instanceof Param) {
            return $this->refactorParam($node);
        }
        return $this->refactorProperty($node);
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::READONLY_PROPERTY;
    }
    private function refactorProperty(Property $property) : ?Property
    {
        // 1. is property read-only?
        if ($this->propertyManipulator->isPropertyChangeableExceptConstructor($property)) {
            return null;
        }
        if ($property->isReadonly()) {
            return null;
        }
        if ($property->props[0]->default instanceof Expr) {
            return null;
        }
        if ($property->type === null) {
            return null;
        }
        if (!$this->visibilityManipulator->hasVisibility($property, Visibility::PRIVATE)) {
            return null;
        }
        if ($property->isStatic()) {
            return null;
        }
        if ($this->propertyFetchAssignManipulator->isAssignedMultipleTimesInConstructor($property)) {
            return null;
        }
        $this->visibilityManipulator->makeReadonly($property);
        $attributeGroups = $property->attrGroups;
        if ($attributeGroups !== []) {
            $property->setAttribute(AttributeKey::ORIGINAL_NODE, null);
        }
        return $property;
    }
    /**
     * @return \PhpParser\Node\Param|null
     */
    private function refactorParam(Param $param)
    {
        if (!$this->visibilityManipulator->hasVisibility($param, Visibility::PRIVATE)) {
            return null;
        }
        if ($param->type === null) {
            return null;
        }
        // promoted property?
        if ($this->propertyManipulator->isPropertyChangeableExceptConstructor($param)) {
            return null;
        }
        if ($this->visibilityManipulator->isReadonly($param)) {
            return null;
        }
        if ($this->paramAnalyzer->isParamReassign($param)) {
            return null;
        }
        $this->visibilityManipulator->makeReadonly($param);
        return $param;
    }
}
