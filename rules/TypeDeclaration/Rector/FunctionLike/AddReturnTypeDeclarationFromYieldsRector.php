<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\FunctionLike;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\Yield_;
use PhpParser\Node\Expr\YieldFrom;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Function_;
use PhpParser\NodeVisitor;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use Rector\NodeTypeResolver\PHPStan\Type\TypeFactory;
use Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser;
use Rector\PHPStan\ScopeFetcher;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\Rector\AbstractRector;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedGenericObjectType;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VendorLocker\NodeVendorLocker\ClassMethodReturnTypeOverrideGuard;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\FunctionLike\AddReturnTypeDeclarationFromYieldsRector\AddReturnTypeDeclarationFromYieldsRectorTest
 */
final class AddReturnTypeDeclarationFromYieldsRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     */
    private TypeFactory $typeFactory;
    /**
     * @readonly
     */
    private SimpleCallableNodeTraverser $simpleCallableNodeTraverser;
    /**
     * @readonly
     */
    private StaticTypeMapper $staticTypeMapper;
    /**
     * @readonly
     */
    private ClassMethodReturnTypeOverrideGuard $classMethodReturnTypeOverrideGuard;
    public function __construct(TypeFactory $typeFactory, SimpleCallableNodeTraverser $simpleCallableNodeTraverser, StaticTypeMapper $staticTypeMapper, ClassMethodReturnTypeOverrideGuard $classMethodReturnTypeOverrideGuard)
    {
        $this->typeFactory = $typeFactory;
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->classMethodReturnTypeOverrideGuard = $classMethodReturnTypeOverrideGuard;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add return type declarations from yields', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function provide()
    {
        yield 1;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    /**
     * @return Iterator<int>
     */
    public function provide(): Iterator
    {
        yield 1;
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
        return [Function_::class, ClassMethod::class];
    }
    /**
     * @param Function_|ClassMethod $node
     */
    public function refactor(Node $node) : ?Node
    {
        $scope = ScopeFetcher::fetch($node);
        $yieldNodes = $this->findCurrentScopeYieldNodes($node);
        if ($yieldNodes === []) {
            return null;
        }
        // skip already filled type
        if ($node->returnType instanceof Node && $this->isNames($node->returnType, ['Iterator', 'Generator', 'Traversable', 'iterable'])) {
            return null;
        }
        if ($node instanceof ClassMethod && $this->classMethodReturnTypeOverrideGuard->shouldSkipClassMethod($node, $scope)) {
            return null;
        }
        $yieldType = $this->resolveYieldType($yieldNodes, $node);
        $returnTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($yieldType, TypeKind::RETURN);
        if (!$returnTypeNode instanceof Node) {
            return null;
        }
        $node->returnType = $returnTypeNode;
        return $node;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::SCALAR_TYPES;
    }
    /**
     * @return Yield_[]|YieldFrom[]
     */
    private function findCurrentScopeYieldNodes(FunctionLike $functionLike) : array
    {
        $yieldNodes = [];
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable((array) $functionLike->getStmts(), static function (Node $node) use(&$yieldNodes) : ?int {
            // skip anonymous class and inner function
            if ($node instanceof Class_) {
                return NodeVisitor::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
            }
            // skip nested scope
            if ($node instanceof FunctionLike) {
                return NodeVisitor::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
            }
            if ($node instanceof Stmt && !$node instanceof Expression) {
                $yieldNodes = [];
                return NodeVisitor::STOP_TRAVERSAL;
            }
            if (!$node instanceof Yield_ && !$node instanceof YieldFrom) {
                return null;
            }
            $yieldNodes[] = $node;
            return null;
        });
        return $yieldNodes;
    }
    /**
     * @param \PhpParser\Node\Expr\Yield_|\PhpParser\Node\Expr\YieldFrom $yield
     */
    private function resolveYieldValue($yield) : ?Expr
    {
        if ($yield instanceof Yield_) {
            return $yield->value;
        }
        return $yield->expr;
    }
    /**
     * @param array<Yield_|YieldFrom> $yieldNodes
     * @return Type[]
     */
    private function resolveYieldedTypes(array $yieldNodes) : array
    {
        $yieldedTypes = [];
        foreach ($yieldNodes as $yieldNode) {
            $value = $this->resolveYieldValue($yieldNode);
            if (!$value instanceof Expr) {
                // one of the yields is empty
                return [];
            }
            $resolvedType = $this->nodeTypeResolver->getType($value);
            if ($resolvedType instanceof MixedType) {
                continue;
            }
            $yieldedTypes[] = $resolvedType;
        }
        return $yieldedTypes;
    }
    /**
     * @param array<Yield_|YieldFrom> $yieldNodes
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_ $functionLike
     * @return \Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType|\Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedGenericObjectType
     */
    private function resolveYieldType(array $yieldNodes, $functionLike)
    {
        $yieldedTypes = $this->resolveYieldedTypes($yieldNodes);
        $className = $this->resolveClassName($functionLike);
        if ($yieldedTypes === []) {
            return new FullyQualifiedObjectType($className);
        }
        $yieldedTypes = $this->typeFactory->createMixedPassedOrUnionType($yieldedTypes);
        return new FullyQualifiedGenericObjectType($className, [$yieldedTypes]);
    }
    /**
     * @param \PhpParser\Node\Stmt\Function_|\PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Expr\Closure $functionLike
     */
    private function resolveClassName($functionLike) : string
    {
        $returnTypeNode = $functionLike->getReturnType();
        if ($returnTypeNode instanceof Identifier && $returnTypeNode->name === 'iterable') {
            return 'Iterator';
        }
        if ($returnTypeNode instanceof Name && !$this->isName($returnTypeNode, 'Generator')) {
            return $this->getName($returnTypeNode);
        }
        return 'Generator';
    }
}
