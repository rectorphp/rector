<?php

declare (strict_types=1);
namespace Rector\TypeDeclarationDocblocks\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Comments\NodeDocBlock\DocBlockUpdater;
use Rector\Rector\AbstractRector;
use Rector\TypeDeclarationDocblocks\NodeFinder\PropertyGetterFinder;
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
    private DocBlockUpdater $docBlockUpdater;
    public function __construct(PhpDocInfoFactory $phpDocInfoFactory, PropertyGetterFinder $propertyGetterFinder, DocBlockUpdater $docBlockUpdater)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->propertyGetterFinder = $propertyGetterFinder;
        $this->docBlockUpdater = $docBlockUpdater;
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
            if ($propertyPhpDocInfo->getVarTagValueNode() instanceof VarTagValueNode) {
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
            $varTagValeNode = new VarTagValueNode($returnTagValueNode->type, '', '');
            // find matching getter and its @return docblock
            $propertyPhpDocInfo->addTagValueNode($varTagValeNode);
            $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($property);
            $hasChanged = \true;
        }
        if (!$hasChanged) {
            return null;
        }
        return $node;
    }
}
