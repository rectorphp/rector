<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\ObjectType;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Comments\NodeDocBlock\DocBlockUpdater;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Rector\ValueObject\MethodName;
/**
 * Shared logic for TypedPropertyFromContainerGetSetUpRector and TypedPropertyFromGetRepositorySetUpRector.
 */
final class SetUpAssignedPropertyTyper
{
    /**
     * @readonly
     */
    private TestsNodeAnalyzer $testsNodeAnalyzer;
    /**
     * @readonly
     */
    private PhpDocInfoFactory $phpDocInfoFactory;
    /**
     * @readonly
     */
    private StaticTypeMapper $staticTypeMapper;
    /**
     * @readonly
     */
    private DocBlockUpdater $docBlockUpdater;
    /**
     * @readonly
     */
    private BetterNodeFinder $betterNodeFinder;
    /**
     * @readonly
     */
    private ReflectionProvider $reflectionProvider;
    /**
     * @readonly
     */
    private NodeNameResolver $nodeNameResolver;
    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer, PhpDocInfoFactory $phpDocInfoFactory, StaticTypeMapper $staticTypeMapper, DocBlockUpdater $docBlockUpdater, BetterNodeFinder $betterNodeFinder, ReflectionProvider $reflectionProvider, NodeNameResolver $nodeNameResolver)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->docBlockUpdater = $docBlockUpdater;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->reflectionProvider = $reflectionProvider;
        $this->nodeNameResolver = $nodeNameResolver;
    }
    /**
     * @param callable(Expr): bool $isTargetAssignExpr
     */
    public function refactorClass(Class_ $class, callable $isTargetAssignExpr): ?Class_
    {
        if (!$this->testsNodeAnalyzer->isInTestClass($class)) {
            return null;
        }
        $setUpClassMethod = $class->getMethod(MethodName::SET_UP);
        if (!$setUpClassMethod instanceof ClassMethod) {
            return null;
        }
        $hasChanged = \false;
        foreach ($class->getProperties() as $property) {
            // type is already set
            if ($property->type instanceof Node) {
                continue;
            }
            if (!$property->isPrivate()) {
                continue;
            }
            if ($property->isStatic()) {
                continue;
            }
            // exactly one property
            if (count($property->props) !== 1) {
                continue;
            }
            $propertyName = $this->nodeNameResolver->getName($property->props[0]);
            if (!$this->isAssignedInSetUp($setUpClassMethod, $propertyName, $isTargetAssignExpr)) {
                continue;
            }
            $propertyPhpDocInfo = $this->phpDocInfoFactory->createFromNode($property);
            if (!$propertyPhpDocInfo instanceof PhpDocInfo) {
                continue;
            }
            $varType = $propertyPhpDocInfo->getVarType();
            if (!$varType instanceof ObjectType) {
                continue;
            }
            $propertyTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($varType, TypeKind::PROPERTY);
            if (!$propertyTypeNode instanceof Name) {
                continue;
            }
            // must be an existing object type
            if (!$this->reflectionProvider->hasClass($propertyTypeNode->toString())) {
                continue;
            }
            $property->type = $propertyTypeNode;
            $this->removeVarTag($propertyPhpDocInfo, $property);
            $hasChanged = \true;
        }
        if ($hasChanged) {
            return $class;
        }
        return null;
    }
    /**
     * @param callable(Expr): bool $isTargetAssignExpr
     */
    private function isAssignedInSetUp(ClassMethod $setUpClassMethod, ?string $propertyName, callable $isTargetAssignExpr): bool
    {
        /** @var Assign[] $assigns */
        $assigns = $this->betterNodeFinder->findInstanceOf($setUpClassMethod, Assign::class);
        foreach ($assigns as $assign) {
            if (!$assign->var instanceof PropertyFetch) {
                continue;
            }
            $propertyFetch = $assign->var;
            if (!$this->nodeNameResolver->isName($propertyFetch->var, 'this')) {
                continue;
            }
            if (!$this->nodeNameResolver->isName($propertyFetch, (string) $propertyName)) {
                continue;
            }
            if ($isTargetAssignExpr($assign->expr)) {
                return \true;
            }
        }
        return \false;
    }
    private function removeVarTag(PhpDocInfo $propertyPhpDocInfo, Property $property): void
    {
        $varTagValueNode = $propertyPhpDocInfo->getVarTagValueNode();
        if (!$varTagValueNode instanceof VarTagValueNode) {
            return;
        }
        $propertyPhpDocInfo->removeByType(VarTagValueNode::class);
        $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($property);
    }
}
