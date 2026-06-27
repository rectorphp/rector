<?php

declare (strict_types=1);
namespace Rector\TypeDeclarationDocblocks\Rector\Class_;

use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Identifier;
use PhpParser\Node\PropertyItem;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\Php\PhpPropertyReflection;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\PHPStan\ScopeFetcher;
use Rector\Rector\AbstractRector;
use Rector\TypeDeclarationDocblocks\NodeDocblockTypeDecorator;
use Rector\TypeDeclarationDocblocks\TagNodeAnalyzer\UsefulArrayTagNodeAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclarationDocblocks\Rector\Class_\DocblockVarArrayFromPropertyDefaultsRector\DocblockVarArrayFromPropertyDefaultsRectorTest
 */
final class DocblockVarArrayFromPropertyDefaultsRector extends AbstractRector
{
    /**
     * @readonly
     */
    private PhpDocInfoFactory $phpDocInfoFactory;
    /**
     * @readonly
     */
    private NodeDocblockTypeDecorator $nodeDocblockTypeDecorator;
    /**
     * @readonly
     */
    private UsefulArrayTagNodeAnalyzer $usefulArrayTagNodeAnalyzer;
    public function __construct(PhpDocInfoFactory $phpDocInfoFactory, NodeDocblockTypeDecorator $nodeDocblockTypeDecorator, UsefulArrayTagNodeAnalyzer $usefulArrayTagNodeAnalyzer)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->nodeDocblockTypeDecorator = $nodeDocblockTypeDecorator;
        $this->usefulArrayTagNodeAnalyzer = $usefulArrayTagNodeAnalyzer;
    }
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add @var array docblock to array property based on iterable default value', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    private array $items = [1, 2, 3];
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    /**
     * @var int[]
     */
    private array $items = [1, 2, 3];
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
            if (!$property->type instanceof Identifier) {
                continue;
            }
            if (!$this->isName($property->type, 'array')) {
                continue;
            }
            if (count($property->props) > 1) {
                continue;
            }
            $soleProperty = $property->props[0];
            if (!$soleProperty->default instanceof Array_) {
                continue;
            }
            $propertyDefaultType = $this->getType($soleProperty->default);
            $propertyPhpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);
            // type is already known
            if ($this->usefulArrayTagNodeAnalyzer->isUsefulArrayTag($propertyPhpDocInfo->getVarTagValueNode())) {
                continue;
            }
            if ($this->hasUsefulParentPropertyVarTag($node, $property, $propertyDefaultType)) {
                continue;
            }
            if ($this->nodeDocblockTypeDecorator->decorateGenericIterableVarType($propertyDefaultType, $propertyPhpDocInfo, $property)) {
                $hasChanged = \true;
            }
        }
        if (!$hasChanged) {
            return null;
        }
        return $node;
    }
    private function hasUsefulParentPropertyVarTag(Class_ $class, Property $property, Type $propertyDefaultType): bool
    {
        $propertyName = $this->getName($property);
        if ($propertyName === null) {
            return \false;
        }
        // private property doesn't override parent property
        if ($property->isPrivate()) {
            return \false;
        }
        $scope = ScopeFetcher::fetch($class);
        $classReflection = $scope->getClassReflection();
        if (!$classReflection instanceof ClassReflection) {
            return \false;
        }
        foreach ($classReflection->getParents() as $parentClassReflection) {
            if (!$parentClassReflection->hasNativeProperty($propertyName)) {
                continue;
            }
            $parentPropertyReflection = $parentClassReflection->getNativeProperty($propertyName);
            if ($parentPropertyReflection->isPrivate()) {
                return \false;
            }
            if (!$parentPropertyReflection->hasPhpDocType()) {
                continue;
            }
            $varTagValueNode = $this->resolveVarTagValueNode($parentPropertyReflection);
            if (!$this->usefulArrayTagNodeAnalyzer->isUsefulArrayTag($varTagValueNode)) {
                continue;
            }
            if ($parentPropertyReflection->getPhpDocType()->isSuperTypeOf($propertyDefaultType)->yes()) {
                return \true;
            }
        }
        return \false;
    }
    private function resolveVarTagValueNode(PhpPropertyReflection $phpPropertyReflection): ?VarTagValueNode
    {
        $docComment = $phpPropertyReflection->getDocComment();
        if ($docComment === null) {
            return null;
        }
        $property = new Property(0, [new PropertyItem($phpPropertyReflection->getName())]);
        $property->setDocComment(new Doc($docComment));
        return $this->phpDocInfoFactory->createFromNodeOrEmpty($property)->getVarTagValueNode();
    }
}
