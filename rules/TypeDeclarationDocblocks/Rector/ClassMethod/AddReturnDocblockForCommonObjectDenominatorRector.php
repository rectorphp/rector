<?php

declare (strict_types=1);
namespace Rector\TypeDeclarationDocblocks\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\ArrayType;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\MixedType;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Rector\Rector\AbstractRector;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use Rector\TypeDeclarationDocblocks\NodeFinder\ReturnNodeFinder;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclarationDocblocks\Rector\ClassMethod\AddReturnDocblockForCommonObjectDenominatorRector\AddReturnDocblockForCommonObjectDenominatorRectorTest
 */
final class AddReturnDocblockForCommonObjectDenominatorRector extends AbstractRector
{
    /**
     * @readonly
     */
    private PhpDocInfoFactory $phpDocInfoFactory;
    /**
     * @readonly
     */
    private ReturnNodeFinder $returnNodeFinder;
    /**
     * @readonly
     */
    private ReflectionProvider $reflectionProvider;
    /**
     * @readonly
     */
    private PhpDocTypeChanger $phpDocTypeChanger;
    public function __construct(PhpDocInfoFactory $phpDocInfoFactory, ReturnNodeFinder $returnNodeFinder, ReflectionProvider $reflectionProvider, PhpDocTypeChanger $phpDocTypeChanger)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->returnNodeFinder = $returnNodeFinder;
        $this->reflectionProvider = $reflectionProvider;
        $this->phpDocTypeChanger = $phpDocTypeChanger;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add @return docblock array of objects, that have common denominator interface/parent class', [new CodeSample(<<<'CODE_SAMPLE'
final class ExtensionProvider
{
    public function getExtensions(): array
    {
        return [
            new FirstExtension(),
            new SecondExtension(),
        ];
    }
}

class FirstExtension implements ExtensionInterface
{
}

class SecondExtension implements ExtensionInterface
{
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class ExtensionProvider
{
    /**
     * @return ExtensionInterface[]
     */
    public function getExtensions(): array
    {
        return [
            new FirstExtension(),
            new SecondExtension(),
        ];
    }
}

class FirstExtension implements ExtensionInterface
{
}

class SecondExtension implements ExtensionInterface
{
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class, Function_::class];
    }
    /**
     * @param ClassMethod|Function_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        $returnType = $phpDocInfo->getReturnType();
        if (!$returnType instanceof MixedType || $returnType->isExplicitMixed()) {
            return null;
        }
        // definitely not an array return
        if ($node->returnType instanceof Node && !$this->isName($node->returnType, 'array')) {
            return null;
        }
        $onlyReturnWithExpr = $this->returnNodeFinder->findOnlyReturnWithExpr($node);
        if (!$onlyReturnWithExpr instanceof Return_ || !$onlyReturnWithExpr->expr instanceof Expr) {
            return null;
        }
        $returnedType = $this->getType($onlyReturnWithExpr->expr);
        if (!$returnedType instanceof ConstantArrayType) {
            return null;
        }
        $referencedClasses = [];
        foreach ($returnedType->getValueTypes() as $valueType) {
            // each item must refer some classes
            if ($valueType->getReferencedClasses() === []) {
                return null;
            }
            /**
             * not an object, can be nested array, or string class as Foo::class
             */
            if (!$valueType->isObject()->yes()) {
                return null;
            }
            $referencedClasses = array_merge($referencedClasses, $valueType->getReferencedClasses());
        }
        // nothing to find here
        if ($referencedClasses === []) {
            return null;
        }
        $parentClassesAndInterfaces = [];
        foreach ($referencedClasses as $referencedClass) {
            $parentClassesAndInterfaces[] = $this->resolveParentClassesAndInterfaces($referencedClass);
        }
        $firstSharedTypes = array_intersect(...$parentClassesAndInterfaces);
        $firstSharedType = $firstSharedTypes[0] ?? null;
        if ($firstSharedType === null) {
            return null;
        }
        $objectTypeArrayType = new ArrayType(new MixedType(), new FullyQualifiedObjectType($firstSharedType));
        $hasChanged = $this->phpDocTypeChanger->changeReturnType($node, $phpDocInfo, $objectTypeArrayType);
        if (!$hasChanged) {
            return null;
        }
        return $node;
    }
    /**
     * @return string[]
     */
    private function resolveParentClassesAndInterfaces(string $className): array
    {
        $referenceClassReflection = $this->reflectionProvider->getClass($className);
        $currentParentClassesAndInterfaces = $referenceClassReflection->getParentClassesNames();
        foreach ($referenceClassReflection->getInterfaces() as $classReflection) {
            $currentParentClassesAndInterfaces[] = $classReflection->getName();
        }
        return $currentParentClassesAndInterfaces;
    }
}
