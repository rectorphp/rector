<?php

declare (strict_types=1);
namespace Rector\PHPUnit\AnnotationsToAttributes\Rector\ClassMethod;

use RectorPrefix202312\Nette\Utils\Json;
use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover;
use Rector\Comments\NodeDocBlock\DocBlockUpdater;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\PhpAttribute\NodeFactory\PhpAttributeGroupFactory;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.phpunit.de/en/10.0/annotations.html#testwith
 * @changelog https://docs.phpunit.de/en/10.2/attributes.html#testwith
 *
 * @see \Rector\PHPUnit\Tests\AnnotationsToAttributes\Rector\ClassMethod\TestWithAnnotationToAttributeRector\TestWithAnnotationToAttributeRectorTest
 */
final class TestWithAnnotationToAttributeRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer
     */
    private $testsNodeAnalyzer;
    /**
     * @readonly
     * @var \Rector\PhpAttribute\NodeFactory\PhpAttributeGroupFactory
     */
    private $phpAttributeGroupFactory;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover
     */
    private $phpDocTagRemover;
    /**
     * @readonly
     * @var \Rector\Comments\NodeDocBlock\DocBlockUpdater
     */
    private $docBlockUpdater;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer, PhpAttributeGroupFactory $phpAttributeGroupFactory, PhpDocTagRemover $phpDocTagRemover, DocBlockUpdater $docBlockUpdater, PhpDocInfoFactory $phpDocInfoFactory)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
        $this->phpAttributeGroupFactory = $phpAttributeGroupFactory;
        $this->phpDocTagRemover = $phpDocTagRemover;
        $this->docBlockUpdater = $docBlockUpdater;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change @testWith() annotation to #[TestWith] attribute', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeFixture extends TestCase
{
    /**
     * @testWith ["foo"]
     *           ["bar"]
     */
    public function test(): void
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\TestWith;

final class SomeFixture extends TestCase
{
    #[TestWith(['foo'])]
    #[TestWith(['bar'])]
    public function test(): void
    {
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
        return [ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->testsNodeAnalyzer->isTestClassMethod($node)) {
            return null;
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNode($node);
        if (!$phpDocInfo instanceof PhpDocInfo) {
            return null;
        }
        $testWithPhpDocTagNodes = \array_merge($phpDocInfo->getTagsByName('testWith'), $phpDocInfo->getTagsByName('testwith'));
        if ($testWithPhpDocTagNodes === []) {
            return null;
        }
        $attributeGroups = [];
        foreach ($testWithPhpDocTagNodes as $testWithPhpDocTagNode) {
            /** @var PhpDocTagNode $testWithPhpDocTagNode */
            if (!$testWithPhpDocTagNode->value instanceof GenericTagValueNode) {
                // not supported yet
                continue;
            }
            // test from doc blocks
            $this->phpDocTagRemover->removeTagValueFromNode($phpDocInfo, $testWithPhpDocTagNode);
            /** @var GenericTagValueNode $genericTagValueNode */
            $genericTagValueNode = $testWithPhpDocTagNode->value;
            $testWithItems = \explode("\n", \trim($genericTagValueNode->value));
            foreach ($testWithItems as $testWithItem) {
                $jsonArray = Json::decode(\trim($testWithItem), Json::FORCE_ARRAY);
                $attributeGroups[] = $this->phpAttributeGroupFactory->createFromClassWithItems('PHPUnit\\Framework\\Attributes\\TestWith', [$jsonArray]);
            }
        }
        $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($node);
        $node->attrGroups = \array_merge($node->attrGroups, $attributeGroups);
        return $node;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::ATTRIBUTES;
    }
}
