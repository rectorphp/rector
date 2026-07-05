<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\ObjectType;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Comments\NodeDocBlock\DocBlockUpdater;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\PHPStan\ScopeFetcher;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\Rector\AbstractRector;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Rector\VendorLocker\NodeVendorLocker\ClassMethodReturnTypeOverrideGuard;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromGetRepositoryDocblockRector\ReturnTypeFromGetRepositoryDocblockRectorTest
 */
final class ReturnTypeFromGetRepositoryDocblockRector extends AbstractRector
{
    /**
     * @readonly
     */
    private BetterNodeFinder $betterNodeFinder;
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
    private ReflectionProvider $reflectionProvider;
    /**
     * @readonly
     */
    private ClassMethodReturnTypeOverrideGuard $classMethodReturnTypeOverrideGuard;
    /**
     * @var string
     */
    private const ENTITY_MANAGER_INTERFACE = 'Doctrine\ORM\EntityManagerInterface';
    public function __construct(BetterNodeFinder $betterNodeFinder, PhpDocInfoFactory $phpDocInfoFactory, StaticTypeMapper $staticTypeMapper, DocBlockUpdater $docBlockUpdater, ReflectionProvider $reflectionProvider, ClassMethodReturnTypeOverrideGuard $classMethodReturnTypeOverrideGuard)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->docBlockUpdater = $docBlockUpdater;
        $this->reflectionProvider = $reflectionProvider;
        $this->classMethodReturnTypeOverrideGuard = $classMethodReturnTypeOverrideGuard;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add repository return type declaration based on @return docblock, when a method returns entity manager getRepository() fetch', [new CodeSample(<<<'CODE_SAMPLE'
final class EventModel
{
    public function __construct(private \Doctrine\ORM\EntityManagerInterface $em)
    {
    }

    /**
     * @return \App\Repository\EventRepository
     */
    public function getRepository()
    {
        return $this->em->getRepository(Event::class);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class EventModel
{
    public function __construct(private \Doctrine\ORM\EntityManagerInterface $em)
    {
    }

    public function getRepository(): \App\Repository\EventRepository
    {
        return $this->em->getRepository(Event::class);
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
        // return type is already set
        if ($node->returnType instanceof Node) {
            return null;
        }
        if (!$this->isGetRepositoryReturnOnly($node)) {
            return null;
        }
        $scope = ScopeFetcher::fetch($node);
        if ($this->classMethodReturnTypeOverrideGuard->shouldSkipClassMethod($node, $scope)) {
            return null;
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNode($node);
        if (!$phpDocInfo instanceof PhpDocInfo) {
            return null;
        }
        $returnType = $phpDocInfo->getReturnType();
        if (!$returnType instanceof ObjectType) {
            return null;
        }
        $returnTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($returnType, TypeKind::RETURN);
        if (!$returnTypeNode instanceof Name) {
            return null;
        }
        // must be an existing repository class
        if (!$this->reflectionProvider->hasClass($returnTypeNode->toString())) {
            return null;
        }
        $node->returnType = $returnTypeNode;
        $this->removeReturnTag($phpDocInfo, $node);
        return $node;
    }
    private function isGetRepositoryReturnOnly(ClassMethod $classMethod): bool
    {
        $returns = $this->betterNodeFinder->findReturnsScoped($classMethod);
        if (count($returns) !== 1) {
            return \false;
        }
        $returnedExpr = $returns[0]->expr;
        // direct return: return $this->em->getRepository(...);
        if ($returnedExpr instanceof MethodCall) {
            return $this->isGetRepositoryMethodCall($returnedExpr);
        }
        // indirect return: $repo = $this->em->getRepository(...); ...; return $repo;
        if ($returnedExpr instanceof Variable) {
            $assignedMethodCall = $this->findVariableAssignMethodCall($classMethod, $returnedExpr);
            if (!$assignedMethodCall instanceof MethodCall) {
                return \false;
            }
            return $this->isGetRepositoryMethodCall($assignedMethodCall);
        }
        return \false;
    }
    private function isGetRepositoryMethodCall(MethodCall $methodCall): bool
    {
        if (!$this->isName($methodCall->name, 'getRepository')) {
            return \false;
        }
        return $this->isEntityManagerType($methodCall);
    }
    private function findVariableAssignMethodCall(ClassMethod $classMethod, Variable $variable): ?MethodCall
    {
        /** @var Assign[] $assigns */
        $assigns = $this->betterNodeFinder->findInstanceOf($classMethod, Assign::class);
        $methodCall = null;
        foreach ($assigns as $assign) {
            if (!$assign->var instanceof Variable) {
                continue;
            }
            if (!$this->nodeComparator->areNodesEqual($assign->var, $variable)) {
                continue;
            }
            // reassigned to something else, cannot be sure of the type
            if (!$assign->expr instanceof MethodCall) {
                return null;
            }
            $methodCall = $assign->expr;
        }
        return $methodCall;
    }
    private function isEntityManagerType(MethodCall $methodCall): bool
    {
        $callerType = $this->nodeTypeResolver->getType($methodCall->var);
        if (!$callerType instanceof ObjectType) {
            return \false;
        }
        if ($callerType->getClassName() === self::ENTITY_MANAGER_INTERFACE) {
            return \true;
        }
        return $callerType->isInstanceOf(self::ENTITY_MANAGER_INTERFACE)->yes();
    }
    private function removeReturnTag(PhpDocInfo $phpDocInfo, ClassMethod $classMethod): void
    {
        $returnTagValueNode = $phpDocInfo->getReturnTagValue();
        if (!$returnTagValueNode instanceof ReturnTagValueNode) {
            return;
        }
        $phpDocInfo->removeByType(ReturnTagValueNode::class);
        $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($classMethod);
    }
}
