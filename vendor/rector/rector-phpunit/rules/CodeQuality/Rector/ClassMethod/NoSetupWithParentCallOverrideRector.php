<?php

declare (strict_types=1);
namespace Rector\PHPUnit\CodeQuality\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Doctrine\NodeAnalyzer\AttributeFinder;
use Rector\PHPUnit\CodeQuality\NodeAnalyser\ParentCallDetector;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\PHPUnit\Tests\CodeQuality\Rector\ClassMethod\NoSetupWithParentCallOverrideRector\NoSetupWithParentCallOverrideRectorTest
 */
final class NoSetupWithParentCallOverrideRector extends AbstractRector
{
    /**
     * @readonly
     */
    private TestsNodeAnalyzer $testsNodeAnalyzer;
    /**
     * @readonly
     */
    private ParentCallDetector $parentCallDetector;
    /**
     * @readonly
     */
    private AttributeFinder $attributeFinder;
    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer, ParentCallDetector $parentCallDetector, AttributeFinder $attributeFinder)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
        $this->parentCallDetector = $parentCallDetector;
        $this->attributeFinder = $attributeFinder;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove override attribute, if setUp()/tearDown() references parent call to improve readability', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $value = 100;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $value = 100;
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
        if (!$this->testsNodeAnalyzer->isInTestClass($node)) {
            return null;
        }
        if (!$this->isNames($node, ['setUp', 'tearDown'])) {
            return null;
        }
        if (!$this->parentCallDetector->hasParentCall($node)) {
            return null;
        }
        if (!$this->attributeFinder->hasAttributeByClasses($node, ['Override'])) {
            return null;
        }
        $hasChanged = \false;
        foreach ($node->attrGroups as $attributeGroupKey => $attrGroup) {
            foreach ($attrGroup->attrs as $attributeKey => $attribute) {
                if (!$this->isName($attribute->name, 'Override')) {
                    continue;
                }
                unset($attrGroup->attrs[$attributeKey]);
                $hasChanged = \true;
            }
            if ($attrGroup->attrs === []) {
                unset($node->attrGroups[$attributeGroupKey]);
            }
        }
        if (!$hasChanged) {
            return null;
        }
        return $node;
    }
}
