<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\ObjectType;
use PHPStan\Type\ObjectWithoutClassType;
use PHPStan\Type\StaticType;
use PHPStan\Type\Type;
use Rector\Enum\ObjectReference;
use Rector\Exception\ShouldNotHappenException;
use Rector\NodeAnalyzer\ClassAnalyzer;
use Rector\NodeTypeResolver\NodeTypeResolver\NewTypeResolver;
use Rector\NodeTypeResolver\PHPStan\Type\TypeFactory;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\PHPStan\ScopeFetcher;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\Rector\AbstractRector;
use Rector\Reflection\ReflectionResolver;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Rector\StaticTypeMapper\ValueObject\Type\SelfStaticType;
use Rector\Symfony\CodeQuality\Enum\ResponseClass;
use Rector\Symfony\TypeAnalyzer\ControllerAnalyzer;
use Rector\TypeDeclaration\NodeAnalyzer\ReturnAnalyzer;
use Rector\TypeDeclaration\NodeAnalyzer\ReturnTypeAnalyzer\StrictReturnNewAnalyzer;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VendorLocker\NodeVendorLocker\ClassMethodReturnTypeOverrideGuard;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromReturnNewRector\ReturnTypeFromReturnNewRectorTest
 */
final class ReturnTypeFromReturnNewRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     */
    private TypeFactory $typeFactory;
    /**
     * @readonly
     */
    private ReflectionProvider $reflectionProvider;
    /**
     * @readonly
     */
    private ReflectionResolver $reflectionResolver;
    /**
     * @readonly
     */
    private StrictReturnNewAnalyzer $strictReturnNewAnalyzer;
    /**
     * @readonly
     */
    private ClassMethodReturnTypeOverrideGuard $classMethodReturnTypeOverrideGuard;
    /**
     * @readonly
     */
    private ClassAnalyzer $classAnalyzer;
    /**
     * @readonly
     */
    private NewTypeResolver $newTypeResolver;
    /**
     * @readonly
     */
    private BetterNodeFinder $betterNodeFinder;
    /**
     * @readonly
     */
    private StaticTypeMapper $staticTypeMapper;
    /**
     * @readonly
     */
    private ReturnAnalyzer $returnAnalyzer;
    /**
     * @readonly
     */
    private ControllerAnalyzer $controllerAnalyzer;
    public function __construct(TypeFactory $typeFactory, ReflectionProvider $reflectionProvider, ReflectionResolver $reflectionResolver, StrictReturnNewAnalyzer $strictReturnNewAnalyzer, ClassMethodReturnTypeOverrideGuard $classMethodReturnTypeOverrideGuard, ClassAnalyzer $classAnalyzer, NewTypeResolver $newTypeResolver, BetterNodeFinder $betterNodeFinder, StaticTypeMapper $staticTypeMapper, ReturnAnalyzer $returnAnalyzer, ControllerAnalyzer $controllerAnalyzer)
    {
        $this->typeFactory = $typeFactory;
        $this->reflectionProvider = $reflectionProvider;
        $this->reflectionResolver = $reflectionResolver;
        $this->strictReturnNewAnalyzer = $strictReturnNewAnalyzer;
        $this->classMethodReturnTypeOverrideGuard = $classMethodReturnTypeOverrideGuard;
        $this->classAnalyzer = $classAnalyzer;
        $this->newTypeResolver = $newTypeResolver;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->returnAnalyzer = $returnAnalyzer;
        $this->controllerAnalyzer = $controllerAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add return type to function like with return new', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public function create()
    {
        return new Project();
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    public function create(): Project
    {
        return new Project();
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
        return [ClassMethod::class, Function_::class];
    }
    /**
     * @param ClassMethod|Function_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        $scope = ScopeFetcher::fetch($node);
        // already filled
        if ($node->returnType instanceof Node) {
            return null;
        }
        if ($node instanceof ClassMethod && $this->classMethodReturnTypeOverrideGuard->shouldSkipClassMethod($node, $scope)) {
            return null;
        }
        $returns = $this->betterNodeFinder->findReturnsScoped($node);
        if (!$this->returnAnalyzer->hasOnlyReturnWithExpr($node, $returns)) {
            return null;
        }
        $returnedNewClassName = $this->strictReturnNewAnalyzer->matchAlwaysReturnVariableNew($node);
        if (\is_string($returnedNewClassName)) {
            $node->returnType = new FullyQualified($returnedNewClassName);
            return $node;
        }
        return $this->refactorDirectReturnNew($node, $returns);
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::SCALAR_TYPES;
    }
    /**
     * @return \PHPStan\Type\ObjectType|\PHPStan\Type\ObjectWithoutClassType|\PHPStan\Type\StaticType|null
     */
    private function createObjectTypeFromNew(New_ $new)
    {
        if ($this->classAnalyzer->isAnonymousClass($new->class)) {
            $newType = $this->newTypeResolver->resolve($new);
            if (!$newType instanceof ObjectWithoutClassType) {
                return null;
            }
            return $newType;
        }
        if (!$new->class instanceof Name) {
            return null;
        }
        $className = $this->getName($new->class);
        if ($className === ObjectReference::STATIC || $className === ObjectReference::SELF) {
            $classReflection = $this->reflectionResolver->resolveClassReflection($new);
            if (!$classReflection instanceof ClassReflection) {
                throw new ShouldNotHappenException();
            }
            if ($className === ObjectReference::SELF) {
                return new SelfStaticType($classReflection);
            }
            return new StaticType($classReflection);
        }
        if (!$this->reflectionProvider->hasClass($className)) {
            return null;
        }
        $classReflection = $this->reflectionProvider->getClass($className);
        return new ObjectType($className, null, $classReflection);
    }
    /**
     * @template TFunctionLike as ClassMethod|Function_
     *
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_ $functionLike
     * @param Return_[] $returns
     * @return TFunctionLike|null
     */
    private function refactorDirectReturnNew($functionLike, array $returns)
    {
        $newTypes = $this->resolveReturnNewType($returns);
        if ($newTypes === null) {
            return null;
        }
        $returnType = $this->typeFactory->createMixedPassedOrUnionType($newTypes);
        /** handled by @see \Rector\Symfony\CodeQuality\Rector\ClassMethod\ResponseReturnTypeControllerActionRector earlier */
        if ($this->isResponseInsideController($returnType, $functionLike)) {
            return null;
        }
        $returnTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($returnType, TypeKind::RETURN);
        if (!$returnTypeNode instanceof Node) {
            return null;
        }
        $functionLike->returnType = $returnTypeNode;
        return $functionLike;
    }
    /**
     * @param Return_[] $returns
     * @return Type[]|null
     */
    private function resolveReturnNewType(array $returns) : ?array
    {
        $newTypes = [];
        foreach ($returns as $return) {
            if (!$return->expr instanceof New_) {
                return null;
            }
            $newType = $this->createObjectTypeFromNew($return->expr);
            if (!$newType instanceof Type) {
                return null;
            }
            $newTypes[] = $newType;
        }
        return $newTypes;
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_ $functionLike
     */
    private function isResponseInsideController(Type $returnType, $functionLike) : bool
    {
        if (!$functionLike instanceof ClassMethod) {
            return \false;
        }
        if (!$returnType instanceof ObjectType) {
            return \false;
        }
        if (!$returnType->isInstanceOf(ResponseClass::BASIC)->yes()) {
            return \false;
        }
        return $this->controllerAnalyzer->isInsideController($functionLike);
    }
}
