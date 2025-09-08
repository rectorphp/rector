<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use PHPStan\Type\MixedType;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Comments\NodeDocBlock\DocBlockUpdater;
use Rector\DeadCode\PhpDoc\DeadVarTagValueNodeAnalyzer;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Rector\TypeDeclaration\AlreadyAssignDetector\ConstructorAssignDetector;
use Rector\ValueObject\MethodName;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\Class_\TypedPropertyFromDocblockSetUpDefinedRector\TypedPropertyFromDocblockSetUpDefinedRectorTest
 */
final class TypedPropertyFromDocblockSetUpDefinedRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     */
    private TestsNodeAnalyzer $testsNodeAnalyzer;
    /**
     * @readonly
     */
    private ConstructorAssignDetector $constructorAssignDetector;
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
    private DeadVarTagValueNodeAnalyzer $deadVarTagValueNodeAnalyzer;
    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer, ConstructorAssignDetector $constructorAssignDetector, PhpDocInfoFactory $phpDocInfoFactory, StaticTypeMapper $staticTypeMapper, DocBlockUpdater $docBlockUpdater, DeadVarTagValueNodeAnalyzer $deadVarTagValueNodeAnalyzer)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
        $this->constructorAssignDetector = $constructorAssignDetector;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->docBlockUpdater = $docBlockUpdater;
        $this->deadVarTagValueNodeAnalyzer = $deadVarTagValueNodeAnalyzer;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add property type in PHPUnit test from docblock, if defined in setUp() method', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

class SomeClass extends TestCase
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $doctrine;

    protected function setUp(): void
    {
        $this->doctrine = $this->container('doctrine.orm.entity_manager');
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

class SomeClass extends TestCase
{
    private \Doctrine\ORM\EntityManagerInterface $doctrine;

    protected function setUp(): void
    {
        $this->doctrine = $this->container('doctrine.orm.entity_manager');
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
        // nothing useful here
        $setUpClassMethod = $node->getMethod(MethodName::SET_UP);
        if (!$setUpClassMethod instanceof ClassMethod) {
            return null;
        }
        $hasChanged = \false;
        foreach ($node->getProperties() as $property) {
            // already known type
            if ($property->type instanceof Node) {
                continue;
            }
            // some magic might be going on
            if ($property->isStatic()) {
                continue;
            }
            if (!$property->isPrivate()) {
                continue;
            }
            // exactly one property
            if (count($property->props) !== 1) {
                continue;
            }
            $propertyName = $property->props[0]->name->toString();
            if (!$this->constructorAssignDetector->isPropertyAssigned($node, $propertyName)) {
                continue;
            }
            $propertyPhpDocInfo = $this->phpDocInfoFactory->createFromNode($property);
            if (!$propertyPhpDocInfo instanceof PhpDocInfo) {
                continue;
            }
            $varType = $propertyPhpDocInfo->getVarType();
            if ($varType instanceof MixedType) {
                continue;
            }
            $nativePropertyTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($varType, TypeKind::PROPERTY);
            if (!$nativePropertyTypeNode instanceof Node) {
                continue;
            }
            $property->type = $nativePropertyTypeNode;
            // remove var tag
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
    private function removeVarTag(PhpDocInfo $propertyPhpDocInfo, Property $property): void
    {
        $varTagValueNode = $propertyPhpDocInfo->getVarTagValueNode();
        if (!$varTagValueNode instanceof VarTagValueNode) {
            return;
        }
        if (!$this->deadVarTagValueNodeAnalyzer->isDead($varTagValueNode, $property)) {
            return;
        }
        $propertyPhpDocInfo->removeByType(VarTagValueNode::class);
        $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($property);
    }
}
