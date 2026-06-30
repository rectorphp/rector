<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
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
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Rector\ValueObject\MethodName;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\Class_\TypedPropertyFromGetRepositorySetUpRector\TypedPropertyFromGetRepositorySetUpRectorTest
 */
final class TypedPropertyFromGetRepositorySetUpRector extends AbstractRector implements MinPhpVersionInterface
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
    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer, PhpDocInfoFactory $phpDocInfoFactory, StaticTypeMapper $staticTypeMapper, DocBlockUpdater $docBlockUpdater, BetterNodeFinder $betterNodeFinder, ReflectionProvider $reflectionProvider)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->docBlockUpdater = $docBlockUpdater;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->reflectionProvider = $reflectionProvider;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add strict typed property to a private test case property based on its @var object type, when assigned via entity manager getRepository() fetch in setUp()', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    /**
     * @var SomeEntityRepository
     */
    private $someEntityRepository;

    protected function setUp(): void
    {
        $this->someEntityRepository = $this->em->getRepository(SomeEntity::class);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    private SomeEntityRepository $someEntityRepository;

    protected function setUp(): void
    {
        $this->someEntityRepository = $this->em->getRepository(SomeEntity::class);
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
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if (!$this->testsNodeAnalyzer->isInTestClass($node)) {
            return null;
        }
        $setUpClassMethod = $node->getMethod(MethodName::SET_UP);
        if (!$setUpClassMethod instanceof ClassMethod) {
            return null;
        }
        $hasChanged = \false;
        foreach ($node->getProperties() as $property) {
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
            $propertyName = $this->getName($property->props[0]);
            if (!$this->isAssignedViaGetRepositoryInSetUp($setUpClassMethod, $propertyName)) {
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
            return $node;
        }
        return null;
    }
    public function provideMinPhpVersion(): int
    {
        return PhpVersionFeature::TYPED_PROPERTIES;
    }
    private function isAssignedViaGetRepositoryInSetUp(ClassMethod $setUpClassMethod, string $propertyName): bool
    {
        /** @var Assign[] $assigns */
        $assigns = $this->betterNodeFinder->findInstanceOf($setUpClassMethod, Assign::class);
        foreach ($assigns as $assign) {
            if (!$assign->var instanceof PropertyFetch) {
                continue;
            }
            $propertyFetch = $assign->var;
            if (!$this->isName($propertyFetch->var, 'this')) {
                continue;
            }
            if (!$this->isName($propertyFetch, $propertyName)) {
                continue;
            }
            if ($this->isGetRepositoryCall($assign->expr)) {
                return \true;
            }
        }
        return \false;
    }
    private function isGetRepositoryCall(Expr $expr): bool
    {
        if (!$expr instanceof MethodCall) {
            return \false;
        }
        return $this->isName($expr->name, 'getRepository');
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
