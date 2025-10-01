<?php

declare (strict_types=1);
namespace Rector\TypeDeclarationDocblocks\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Rector\AbstractRector;
use Rector\TypeDeclarationDocblocks\NodeDocblockTypeDecorator;
use Rector\TypeDeclarationDocblocks\NodeFinder\PropertyGetterFinder;
use Rector\TypeDeclarationDocblocks\TagNodeAnalyzer\UsefulArrayTagNodeAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclarationDocblocks\Rector\Class_\DocblockVarArrayFromGetterReturnRector\DocblockVarArrayFromGetterReturnRectorTest
 */
final class DocblockVarArrayFromGetterReturnRector extends AbstractRector
{
    /**
     * @readonly
     */
    private PhpDocInfoFactory $phpDocInfoFactory;
    /**
     * @readonly
     */
    private PropertyGetterFinder $propertyGetterFinder;
    /**
     * @readonly
     */
    private UsefulArrayTagNodeAnalyzer $usefulArrayTagNodeAnalyzer;
    /**
     * @readonly
     */
    private NodeDocblockTypeDecorator $nodeDocblockTypeDecorator;
    public function __construct(PhpDocInfoFactory $phpDocInfoFactory, PropertyGetterFinder $propertyGetterFinder, UsefulArrayTagNodeAnalyzer $usefulArrayTagNodeAnalyzer, NodeDocblockTypeDecorator $nodeDocblockTypeDecorator)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->propertyGetterFinder = $propertyGetterFinder;
        $this->usefulArrayTagNodeAnalyzer = $usefulArrayTagNodeAnalyzer;
        $this->nodeDocblockTypeDecorator = $nodeDocblockTypeDecorator;
    }
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add @var array property docblock from its getter @return', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    private array $items;

    /**
     * @return int[]
     */
    public function getItems(): array
    {
        return $this->items;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    /**
     * @var int[]
     */
    private array $items;

    /**
     * @return int[]
     */
    public function getItems(): array
    {
        return $this->items;
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
        $hasChanged = \false;
        foreach ($node->getProperties() as $property) {
            // property type must be known
            if (!$property->type instanceof Identifier) {
                continue;
            }
            if (!$this->isName($property->type, 'array')) {
                continue;
            }
            if (count($property->props) > 1) {
                continue;
            }
            $propertyPhpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);
            // type is already known, skip it
            if ($this->usefulArrayTagNodeAnalyzer->isUsefulArrayTag($propertyPhpDocInfo->getVarTagValueNode())) {
                continue;
            }
            $propertyGetterMethod = $this->propertyGetterFinder->find($property, $node);
            if (!$propertyGetterMethod instanceof ClassMethod) {
                continue;
            }
            $classMethodDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($propertyGetterMethod);
            $returnTagValueNode = $classMethodDocInfo->getReturnTagValue();
            if (!$returnTagValueNode instanceof ReturnTagValueNode) {
                continue;
            }
            $isPropertyChanged = $this->nodeDocblockTypeDecorator->decorateGenericIterableVarType($classMethodDocInfo->getReturnType(), $propertyPhpDocInfo, $property);
            if (!$isPropertyChanged) {
                continue;
            }
            $hasChanged = \true;
        }
        if (!$hasChanged) {
            return null;
        }
        return $node;
    }
}
