<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\GenericTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\ObjectType;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\ValueObject\Type\FullyQualifiedIdentifierTypeNode;
use Rector\Comments\NodeDocBlock\DocBlockUpdater;
use Rector\NodeTypeResolver\TypeComparator\TypeComparator;
use Rector\PhpParser\AstResolver;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\Rector\AbstractRector;
use Rector\Reflection\ReflectionResolver;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * Narrows return type from generic object to specific class in final classes/methods.
 *
 * @see \Rector\Tests\TypeDeclaration\Rector\ClassMethod\NarrowObjectReturnTypeRector\NarrowObjectReturnTypeRectorTest
 */
final class NarrowObjectReturnTypeRector extends AbstractRector
{
    /**
     * @readonly
     */
    private BetterNodeFinder $betterNodeFinder;
    /**
     * @readonly
     */
    private ReflectionResolver $reflectionResolver;
    /**
     * @readonly
     */
    private AstResolver $astResolver;
    /**
     * @readonly
     */
    private StaticTypeMapper $staticTypeMapper;
    /**
     * @readonly
     */
    private TypeComparator $typeComparator;
    /**
     * @readonly
     */
    private PhpDocInfoFactory $phpDocInfoFactory;
    /**
     * @readonly
     */
    private DocBlockUpdater $docBlockUpdater;
    public function __construct(BetterNodeFinder $betterNodeFinder, ReflectionResolver $reflectionResolver, AstResolver $astResolver, StaticTypeMapper $staticTypeMapper, TypeComparator $typeComparator, PhpDocInfoFactory $phpDocInfoFactory, DocBlockUpdater $docBlockUpdater)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->reflectionResolver = $reflectionResolver;
        $this->astResolver = $astResolver;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->typeComparator = $typeComparator;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->docBlockUpdater = $docBlockUpdater;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Narrows return type from generic object to specific class in final classes/methods', [new CodeSample(<<<'CODE_SAMPLE'
final class TalkFactory extends AbstractFactory
{
    protected function build(): object
    {
        return new ConferenceTalk();
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class TalkFactory extends AbstractFactory
{
    protected function build(): ConferenceTalk
    {
        return new ConferenceTalk();
    }
}
CODE_SAMPLE
), new CodeSample(<<<'CODE_SAMPLE'
final class TalkFactory
{
    public function createConferenceTalk(): Talk
    {
        return new ConferenceTalk();
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class TalkFactory
{
    public function createConferenceTalk(): ConferenceTalk
    {
        return new ConferenceTalk();
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        $returnType = $node->returnType;
        if (!$returnType instanceof Identifier && !$returnType instanceof FullyQualified) {
            return null;
        }
        $classReflection = $this->reflectionResolver->resolveClassReflection($node);
        if (!$classReflection instanceof ClassReflection) {
            return null;
        }
        if (!$classReflection->isFinalByKeyword() && !$node->isFinal()) {
            return null;
        }
        if ($this->hasParentMethodWithNonObjectReturn($node)) {
            return null;
        }
        $actualReturnClass = $this->getActualReturnClass($node);
        if ($actualReturnClass === null) {
            return null;
        }
        $declaredType = $returnType->toString();
        if ($declaredType === $actualReturnClass) {
            return null;
        }
        if ($this->isDeclaredTypeFinal($declaredType)) {
            return null;
        }
        if ($this->isActualTypeAnonymous($actualReturnClass)) {
            return null;
        }
        if (!$this->isNarrowingValid($declaredType, $actualReturnClass)) {
            return null;
        }
        $node->returnType = new FullyQualified($actualReturnClass);
        $this->updateDocblock($node, $actualReturnClass);
        return $node;
    }
    private function updateDocblock(ClassMethod $classMethod, string $actualReturnClass): void
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNode($classMethod);
        if (!$phpDocInfo instanceof PhpDocInfo) {
            return;
        }
        $returnTagValueNode = $phpDocInfo->getReturnTagValue();
        if (!$returnTagValueNode instanceof ReturnTagValueNode) {
            return;
        }
        if ($returnTagValueNode->type instanceof IdentifierTypeNode) {
            $oldType = $this->staticTypeMapper->mapPHPStanPhpDocTypeNodeToPHPStanType($returnTagValueNode->type, $classMethod);
        } elseif ($returnTagValueNode->type instanceof GenericTypeNode) {
            $oldType = $this->staticTypeMapper->mapPHPStanPhpDocTypeNodeToPHPStanType($returnTagValueNode->type->type, $classMethod);
        } else {
            return;
        }
        if ($oldType instanceof ObjectType) {
            $objectType = new ObjectType($actualReturnClass);
            if ($this->typeComparator->areTypesEqual($oldType, $objectType)) {
                return;
            }
        }
        if ($returnTagValueNode->type instanceof IdentifierTypeNode) {
            $returnTagValueNode->type = new FullyQualifiedIdentifierTypeNode($actualReturnClass);
        } else {
            $returnTagValueNode->type->type = new FullyQualifiedIdentifierTypeNode($actualReturnClass);
        }
        $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($classMethod);
    }
    private function isDeclaredTypeFinal(string $declaredType): bool
    {
        if ($declaredType === 'object') {
            return \false;
        }
        $declaredObjectType = new ObjectType($declaredType);
        $classReflection = $declaredObjectType->getClassReflection();
        if (!$classReflection instanceof ClassReflection) {
            return \false;
        }
        return $classReflection->isFinalByKeyword();
    }
    private function isActualTypeAnonymous(string $actualType): bool
    {
        $actualObjectType = new ObjectType($actualType);
        $classReflection = $actualObjectType->getClassReflection();
        if (!$classReflection instanceof ClassReflection) {
            return \false;
        }
        return $classReflection->isAnonymous();
    }
    private function isNarrowingValid(string $declaredType, string $actualType): bool
    {
        if ($declaredType === 'object') {
            return \true;
        }
        $actualObjectType = new ObjectType($actualType);
        $declaredObjectType = new ObjectType($declaredType);
        return $declaredObjectType->isSuperTypeOf($actualObjectType)->yes();
    }
    private function hasParentMethodWithNonObjectReturn(ClassMethod $classMethod): bool
    {
        if ($classMethod->isPrivate()) {
            return \false;
        }
        $classReflection = $this->reflectionResolver->resolveClassReflection($classMethod);
        if (!$classReflection instanceof ClassReflection) {
            return \false;
        }
        $ancestors = array_filter($classReflection->getAncestors(), fn(ClassReflection $ancestorClassReflection): bool => $classReflection->getName() !== $ancestorClassReflection->getName());
        $methodName = $this->getName($classMethod);
        foreach ($ancestors as $ancestor) {
            if ($ancestor->getFileName() === null) {
                continue;
            }
            if (!$ancestor->hasNativeMethod($methodName)) {
                continue;
            }
            $parentClassMethod = $this->astResolver->resolveClassMethod($ancestor->getName(), $methodName);
            if (!$parentClassMethod instanceof ClassMethod) {
                continue;
            }
            $parentReturnType = $parentClassMethod->returnType;
            if (!$parentReturnType instanceof Node) {
                continue;
            }
            if ($parentReturnType instanceof Identifier && $parentReturnType->name === 'object') {
                continue;
            }
            return \true;
        }
        return \false;
    }
    private function getActualReturnClass(ClassMethod $classMethod): ?string
    {
        $returnStatements = $this->betterNodeFinder->findReturnsScoped($classMethod);
        if ($returnStatements === []) {
            return null;
        }
        $returnedClass = null;
        foreach ($returnStatements as $returnStatement) {
            if ($returnStatement->expr === null) {
                return null;
            }
            $returnType = $this->nodeTypeResolver->getNativeType($returnStatement->expr);
            if (!$returnType->isObject()->yes()) {
                return null;
            }
            $classNames = $returnType->getObjectClassNames();
            if (count($classNames) !== 1) {
                return null;
            }
            $className = $classNames[0];
            if ($returnedClass === null) {
                $returnedClass = $className;
            } elseif ($returnedClass !== $className) {
                return null;
            }
        }
        return $returnedClass;
    }
}
