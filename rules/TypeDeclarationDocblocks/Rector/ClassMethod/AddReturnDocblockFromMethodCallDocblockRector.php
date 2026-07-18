<?php

declare (strict_types=1);
namespace Rector\TypeDeclarationDocblocks\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Rector\Doctrine\Enum\DoctrineClass;
use Rector\Rector\AbstractRector;
use Rector\Reflection\ReflectionResolver;
use Rector\TypeDeclarationDocblocks\NodeFinder\ReturnNodeFinder;
use Rector\TypeDeclarationDocblocks\TagNodeAnalyzer\UsefulArrayTagNodeAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclarationDocblocks\Rector\ClassMethod\AddReturnDocblockFromMethodCallDocblockRector\AddReturnDocblockFromMethodCallDocblockRectorTest
 */
final class AddReturnDocblockFromMethodCallDocblockRector extends AbstractRector
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
    private UsefulArrayTagNodeAnalyzer $usefulArrayTagNodeAnalyzer;
    /**
     * @readonly
     */
    private ReflectionResolver $reflectionResolver;
    /**
     * @readonly
     */
    private PhpDocTypeChanger $phpDocTypeChanger;
    public function __construct(PhpDocInfoFactory $phpDocInfoFactory, ReturnNodeFinder $returnNodeFinder, UsefulArrayTagNodeAnalyzer $usefulArrayTagNodeAnalyzer, ReflectionResolver $reflectionResolver, PhpDocTypeChanger $phpDocTypeChanger)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->returnNodeFinder = $returnNodeFinder;
        $this->usefulArrayTagNodeAnalyzer = $usefulArrayTagNodeAnalyzer;
        $this->reflectionResolver = $reflectionResolver;
        $this->phpDocTypeChanger = $phpDocTypeChanger;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add @return docblock based on detailed type of method call docblock', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeController
{
    public function getAll(): array
    {
        return $this->repository->findAll();
    }
}

final class Repository
{
    /**
     * @return SomeEntity[]
     */
    public function findAll(): array
    {
        // ...
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeController
{
    /**
     * @return SomeEntity[]
     */
    public function getAll(): array
    {
        return $this->repository->findAll();
    }
}

final class Repository
{
    /**
     * @return SomeEntity[]
     */
    public function findAll(): array
    {
        // ...
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
        // definitely not an array return
        if (!$node->returnType instanceof Node || !$this->isName($node->returnType, 'array')) {
            return null;
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        if ($this->usefulArrayTagNodeAnalyzer->isUsefulArrayTag($phpDocInfo->getReturnTagValue())) {
            return null;
        }
        $onlyReturnWithExpr = $this->returnNodeFinder->findOnlyReturnWithExpr($node);
        if (!$onlyReturnWithExpr instanceof Return_ || !$onlyReturnWithExpr->expr instanceof MethodCall && !$onlyReturnWithExpr->expr instanceof StaticCall) {
            return null;
        }
        $returnedMethodCall = $onlyReturnWithExpr->expr;
        // skip doctrine connection calls, as to generic and not helpful
        $callerType = $this->getType($returnedMethodCall instanceof MethodCall ? $returnedMethodCall->var : $returnedMethodCall->class);
        if ($callerType instanceof ObjectType && $callerType->isInstanceOf(DoctrineClass::CONNECTION)->yes()) {
            return null;
        }
        $calledMethodReflection = $this->reflectionResolver->resolveFunctionLikeReflectionFromCall($returnedMethodCall);
        if (!$calledMethodReflection instanceof MethodReflection) {
            return null;
        }
        $extendedParametersAcceptor = ParametersAcceptorSelector::combineAcceptors($calledMethodReflection->getVariants());
        // native return type must be a plain array
        if (!$extendedParametersAcceptor->getNativeReturnType()->isArray()->yes()) {
            return null;
        }
        // docblock must carry a more specific array value type, e.g. SomeEntity[]
        $calledReturnType = $extendedParametersAcceptor->getReturnType();
        if ($calledReturnType->getIterableValueType() instanceof MixedType) {
            return null;
        }
        $this->phpDocTypeChanger->changeReturnType($node, $phpDocInfo, $calledReturnType);
        return $node;
    }
}
