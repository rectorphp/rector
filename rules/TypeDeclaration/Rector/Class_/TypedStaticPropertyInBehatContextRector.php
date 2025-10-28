<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PHPStan\Type\ObjectType;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\DeadCode\PhpDoc\TagRemover\VarTagRemover;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\PHPUnit\Enum\BehatClassName;
use Rector\Rector\AbstractRector;
use Rector\StaticTypeMapper\ValueObject\Type\ShortenedObjectType;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\Class_\TypedStaticPropertyInBehatContextRectorTest\TypedStaticPropertyInBehatContextRectorTest
 */
final class TypedStaticPropertyInBehatContextRector extends AbstractRector
{
    /**
     * @readonly
     */
    private PhpDocInfoFactory $phpDocInfoFactory;
    /**
     * @readonly
     */
    private VarTagRemover $varTagRemover;
    /**
     * @readonly
     */
    private ValueResolver $valueResolver;
    public function __construct(PhpDocInfoFactory $phpDocInfoFactory, VarTagRemover $varTagRemover, ValueResolver $valueResolver)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->varTagRemover = $varTagRemover;
        $this->valueResolver = $valueResolver;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add known property types to Behat context static properties', [new CodeSample(<<<'CODE_SAMPLE'
use Behat\Behat\Context\Context;

final class FeatureContext implements Context
{
    /**
     * @var SomeObject
     */
    public static $someStaticProperty;
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Behat\Behat\Context\Context;

final class FeatureContext implements Context
{
    public static ?SomeObject $someStaticProperty = null;
}
CODE_SAMPLE
)]);
    }
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        // no parents
        if (!$node->extends instanceof Name && $node->implements === []) {
            return null;
        }
        if (!$this->isObjectType($node, new ObjectType(BehatClassName::CONTEXT))) {
            return null;
        }
        $hasChanged = \false;
        foreach ($node->getProperties() as $property) {
            if ($property->type instanceof Node) {
                continue;
            }
            if (!$property->isStatic()) {
                continue;
            }
            if ($this->hasNonNullDefault($property)) {
                continue;
            }
            $propertyPhpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);
            $varType = $propertyPhpDocInfo->getVarType();
            if (!$varType instanceof ObjectType) {
                continue;
            }
            if ($varType instanceof ShortenedObjectType) {
                $className = $varType->getFullyQualifiedName();
            } else {
                $className = $varType->getClassName();
            }
            $property->type = new NullableType(new FullyQualified($className));
            if (!$property->props[0]->default instanceof Node) {
                $property->props[0]->default = $this->nodeFactory->createNull();
            }
            $this->varTagRemover->removeVarTagIfUseless($propertyPhpDocInfo, $property);
            $hasChanged = \true;
        }
        if (!$hasChanged) {
            return null;
        }
        return $node;
    }
    private function hasNonNullDefault(Property $property): bool
    {
        $soleProperty = $property->props[0];
        if (!$soleProperty->default instanceof Expr) {
            return \false;
        }
        return !$this->valueResolver->isNull($soleProperty->default);
    }
}
