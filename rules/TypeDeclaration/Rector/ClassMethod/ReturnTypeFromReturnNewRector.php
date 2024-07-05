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
use PHPStan\Analyser\Scope;
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
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\Rector\AbstractScopeAwareRector;
use Rector\Reflection\ReflectionResolver;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Rector\StaticTypeMapper\ValueObject\Type\SelfStaticType;
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
final class ReturnTypeFromReturnNewRector extends AbstractScopeAwareRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\PHPStan\Type\TypeFactory
     */
    private $typeFactory;
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    /**
     * @readonly
     * @var \Rector\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\NodeAnalyzer\ReturnTypeAnalyzer\StrictReturnNewAnalyzer
     */
    private $strictReturnNewAnalyzer;
    /**
     * @readonly
     * @var \Rector\VendorLocker\NodeVendorLocker\ClassMethodReturnTypeOverrideGuard
     */
    private $classMethodReturnTypeOverrideGuard;
    /**
     * @readonly
     * @var \Rector\NodeAnalyzer\ClassAnalyzer
     */
    private $classAnalyzer;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeResolver\NewTypeResolver
     */
    private $newTypeResolver;
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\StaticTypeMapper\StaticTypeMapper
     */
    private $staticTypeMapper;
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\NodeAnalyzer\ReturnAnalyzer
     */
    private $returnAnalyzer;
    public function __construct(TypeFactory $typeFactory, ReflectionProvider $reflectionProvider, ReflectionResolver $reflectionResolver, StrictReturnNewAnalyzer $strictReturnNewAnalyzer, ClassMethodReturnTypeOverrideGuard $classMethodReturnTypeOverrideGuard, ClassAnalyzer $classAnalyzer, NewTypeResolver $newTypeResolver, BetterNodeFinder $betterNodeFinder, StaticTypeMapper $staticTypeMapper, ReturnAnalyzer $returnAnalyzer)
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
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add return type to function like with return new', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public function action()
    {
        return new Response();
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    public function action(): Response
    {
        return new Response();
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
    public function refactorWithScope(Node $node, Scope $scope) : ?Node
    {
        // already filled
        if ($node->returnType !== null) {
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
}
