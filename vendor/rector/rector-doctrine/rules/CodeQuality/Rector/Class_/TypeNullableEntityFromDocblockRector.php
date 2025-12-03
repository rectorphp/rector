<?php

declare (strict_types=1);
namespace Rector\Doctrine\CodeQuality\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Comments\NodeDocBlock\DocBlockUpdater;
use Rector\DeadCode\PhpDoc\DeadVarTagValueNodeAnalyzer;
use Rector\Doctrine\CodeQuality\Helper\SetterGetterFinder;
use Rector\Doctrine\NodeAnalyzer\DoctrineEntityDetector;
use Rector\Doctrine\NodeManipulator\ColumnPropertyTypeResolver;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\Rector\AbstractRector;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Doctrine\Tests\CodeQuality\Rector\Class_\TypeNullableEntityFromDocblockRector\TypeNullableEntityFromDocblockRectorTest
 */
final class TypeNullableEntityFromDocblockRector extends AbstractRector
{
    /**
     * @readonly
     */
    private ColumnPropertyTypeResolver $columnPropertyTypeResolver;
    /**
     * @readonly
     */
    private StaticTypeMapper $staticTypeMapper;
    /**
     * @readonly
     */
    private DeadVarTagValueNodeAnalyzer $deadVarTagValueNodeAnalyzer;
    /**
     * @readonly
     */
    private PhpDocInfoFactory $phpDocInfoFactory;
    /**
     * @readonly
     */
    private DocBlockUpdater $docBlockUpdater;
    /**
     * @readonly
     */
    private SetterGetterFinder $setterGetterFinder;
    /**
     * @readonly
     */
    private DoctrineEntityDetector $doctrineEntityDetector;
    public function __construct(ColumnPropertyTypeResolver $columnPropertyTypeResolver, StaticTypeMapper $staticTypeMapper, DeadVarTagValueNodeAnalyzer $deadVarTagValueNodeAnalyzer, PhpDocInfoFactory $phpDocInfoFactory, DocBlockUpdater $docBlockUpdater, SetterGetterFinder $setterGetterFinder, DoctrineEntityDetector $doctrineEntityDetector)
    {
        $this->columnPropertyTypeResolver = $columnPropertyTypeResolver;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->deadVarTagValueNodeAnalyzer = $deadVarTagValueNodeAnalyzer;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->docBlockUpdater = $docBlockUpdater;
        $this->setterGetterFinder = $setterGetterFinder;
        $this->doctrineEntityDetector = $doctrineEntityDetector;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add full nullable type coverage for Doctrine entity based on docblocks. Useful stepping stone to add type coverage while keeping entities safe to read and write getter/setters', [new CodeSample(<<<'CODE_SAMPLE'
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class SomeEntity
{
    /**
     * @var string
     * @ORM\Id
     * @ORM\Column(type="string")
     * @ORM\GeneratedValue
     */
    private $name;


    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name): void
    {
        $this->name = $name;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class SomeEntity
{
    /**
     * @ORM\Id
     * @ORM\Column(type="string")
     * @ORM\GeneratedValue
     */
    private ?string $name = null;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }
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
    public function refactor(Node $node): ?Class_
    {
        if (!$this->doctrineEntityDetector->detect($node)) {
            return null;
        }
        $hasChanged = \false;
        foreach ($node->getProperties() as $property) {
            // property is already typed, skip
            if ($property->type instanceof Node) {
                continue;
            }
            $propertyType = $this->columnPropertyTypeResolver->resolve($property, \true);
            if (!$propertyType instanceof Type) {
                continue;
            }
            if ($propertyType instanceof MixedType) {
                continue;
            }
            $propertyTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($propertyType, TypeKind::PROPERTY);
            if (!$propertyTypeNode instanceof Node) {
                continue;
            }
            $property->type = $propertyTypeNode;
            $propertyItem = $property->props[0];
            // set default value to null if nullable
            if (!$propertyItem->default instanceof Expr) {
                $propertyItem->default = new ConstFetch(new Name('null'));
            }
            $hasChanged = \true;
            $this->removeVarTagIfNotUseful($property);
            $propertyName = $this->getName($property);
            $this->decorateGetterClassMethodReturnType($node, $propertyName, $propertyType);
            $this->decorateSetterClassMethodParameterType($node, $propertyName, $propertyType);
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    private function removeVarTagIfNotUseful(Property $property): void
    {
        // remove @var docblock if not useful
        $propertyPhpDocInfo = $this->phpDocInfoFactory->createFromNode($property);
        if (!$propertyPhpDocInfo instanceof PhpDocInfo) {
            return;
        }
        $propertyVarTagValueNode = $propertyPhpDocInfo->getVarTagValueNode();
        if (!$propertyVarTagValueNode instanceof VarTagValueNode) {
            return;
        }
        if (!$this->deadVarTagValueNodeAnalyzer->isDead($propertyVarTagValueNode, $property)) {
            return;
        }
        $propertyPhpDocInfo->removeByType(VarTagValueNode::class);
        $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($property);
    }
    private function decorateGetterClassMethodReturnType(Class_ $class, string $propertyName, Type $propertyType): void
    {
        $getterClassMethod = $this->setterGetterFinder->findGetterClassMethod($class, $propertyName);
        if (!$getterClassMethod instanceof ClassMethod) {
            return;
        }
        // already known type
        if ($getterClassMethod->returnType instanceof Node) {
            return;
        }
        $returnTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($propertyType, TypeKind::RETURN);
        // no type to fill
        if (!$returnTypeNode instanceof Node) {
            return;
        }
        $getterClassMethod->returnType = $returnTypeNode;
        $this->removeReturnDocblock($getterClassMethod);
    }
    private function decorateSetterClassMethodParameterType(Class_ $class, string $propertyName, Type $propertyType): void
    {
        $setterClassMethod = $this->setterGetterFinder->findSetterClassMethod($class, $propertyName);
        if (!$setterClassMethod instanceof ClassMethod) {
            return;
        }
        if (count($setterClassMethod->params) !== 1) {
            return;
        }
        $soleParam = $setterClassMethod->params[0];
        // already known type
        if ($soleParam->type instanceof Node) {
            return;
        }
        $parameterTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($propertyType, TypeKind::PARAM);
        // no type to fill
        if (!$parameterTypeNode instanceof Node) {
            return;
        }
        $soleParam->type = $parameterTypeNode;
        $this->removeParamDocblock($setterClassMethod);
    }
    private function removeParamDocblock(ClassMethod $classMethod): void
    {
        $classMethodPhpDocInfo = $this->phpDocInfoFactory->createFromNode($classMethod);
        if (!$classMethodPhpDocInfo instanceof PhpDocInfo) {
            return;
        }
        if ($classMethodPhpDocInfo->getParamTagValueNodes() === []) {
            return;
        }
        $paramTagValueNode = $classMethodPhpDocInfo->getParamTagValueNodes()[0] ?? null;
        if ($paramTagValueNode instanceof ParamTagValueNode && $paramTagValueNode->description !== '') {
            return;
        }
        $classMethodPhpDocInfo->removeByType(ParamTagValueNode::class);
        $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($classMethod);
    }
    private function removeReturnDocblock(ClassMethod $classMethod): void
    {
        $classMethodPhpDocInfo = $this->phpDocInfoFactory->createFromNode($classMethod);
        if (!$classMethodPhpDocInfo instanceof PhpDocInfo) {
            return;
        }
        if (!$classMethodPhpDocInfo->getReturnTagValue() instanceof ReturnTagValueNode) {
            return;
        }
        if ($classMethodPhpDocInfo->getReturnTagValue()->description !== '') {
            return;
        }
        $classMethodPhpDocInfo->removeByType(ReturnTagValueNode::class);
        $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($classMethod);
    }
}
