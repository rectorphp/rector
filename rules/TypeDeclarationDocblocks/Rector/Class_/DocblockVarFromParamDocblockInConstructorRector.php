<?php

declare (strict_types=1);
namespace Rector\TypeDeclarationDocblocks\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PHPStan\Type\ArrayType;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Rector\AbstractRector;
use Rector\TypeDeclarationDocblocks\NodeAnalyzer\ConstructorAssignedTypeResolver;
use Rector\TypeDeclarationDocblocks\NodeDocblockTypeDecorator;
use Rector\TypeDeclarationDocblocks\TagNodeAnalyzer\UsefulArrayTagNodeAnalyzer;
use Rector\ValueObject\MethodName;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclarationDocblocks\Rector\Class_\DocblockVarFromParamDocblockInConstructorRector\DocblockVarFromParamDocblockInConstructorRectorTest
 */
final class DocblockVarFromParamDocblockInConstructorRector extends AbstractRector
{
    /**
     * @readonly
     */
    private PhpDocInfoFactory $phpDocInfoFactory;
    /**
     * @readonly
     */
    private ConstructorAssignedTypeResolver $constructorAssignedTypeResolver;
    /**
     * @readonly
     */
    private UsefulArrayTagNodeAnalyzer $usefulArrayTagNodeAnalyzer;
    /**
     * @readonly
     */
    private NodeDocblockTypeDecorator $nodeDocblockTypeDecorator;
    public function __construct(PhpDocInfoFactory $phpDocInfoFactory, ConstructorAssignedTypeResolver $constructorAssignedTypeResolver, UsefulArrayTagNodeAnalyzer $usefulArrayTagNodeAnalyzer, NodeDocblockTypeDecorator $nodeDocblockTypeDecorator)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->constructorAssignedTypeResolver = $constructorAssignedTypeResolver;
        $this->usefulArrayTagNodeAnalyzer = $usefulArrayTagNodeAnalyzer;
        $this->nodeDocblockTypeDecorator = $nodeDocblockTypeDecorator;
    }
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add @var array docblock to a property based on @param of constructor assign', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    private array $items;

    /**
     * @param string[] $items
     */
    public function __construct(array $items)
    {
        $this->items = $items;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    /**
     * @var string[]
     */
    private array $items;

    /**
     * @param string[] $items
     */
    public function __construct(array $items)
    {
        $this->items = $items;
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $constructorClassMethod = $node->getMethod(MethodName::CONSTRUCT);
        if (!$constructorClassMethod instanceof ClassMethod) {
            return null;
        }
        $hasChanged = \false;
        foreach ($node->getProperties() as $property) {
            if (!$this->isArrayTypedProperty($property)) {
                continue;
            }
            $propertyPhpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);
            // @var tag already given
            if ($this->usefulArrayTagNodeAnalyzer->isUsefulArrayTag($propertyPhpDocInfo->getVarTagValueNode())) {
                continue;
            }
            $propertyName = $this->getName($property);
            $assignedType = $this->constructorAssignedTypeResolver->resolve($node, $propertyName);
            if (!$assignedType instanceof ArrayType) {
                continue;
            }
            $hasPropertyChanged = $this->nodeDocblockTypeDecorator->decorateGenericIterableVarType($assignedType, $propertyPhpDocInfo, $property);
            if (!$hasPropertyChanged) {
                continue;
            }
            $hasChanged = \true;
        }
        if (!$hasChanged) {
            return null;
        }
        return $node;
    }
    private function isArrayTypedProperty(Property $property): bool
    {
        if (!$property->type instanceof Node) {
            return \false;
        }
        return $this->isName($property->type, 'array');
    }
}
