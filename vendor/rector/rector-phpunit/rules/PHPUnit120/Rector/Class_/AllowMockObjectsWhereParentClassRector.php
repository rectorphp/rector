<?php

declare (strict_types=1);
namespace Rector\PHPUnit\PHPUnit120\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Attribute;
use PhpParser\Node\AttributeGroup;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ReflectionProvider;
use Rector\Doctrine\NodeAnalyzer\AttributeFinder;
use Rector\PHPStan\ScopeFetcher;
use Rector\PHPUnit\Enum\PHPUnitAttribute;
use Rector\PHPUnit\Enum\PHPUnitClassName;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\PHPUnit\Tests\PHPUnit120\Rector\Class_\AllowMockObjectsWhereParentClassRector\AllowMockObjectsWhereParentClassRectorTest
 *
 * @see https://github.com/sebastianbergmann/phpunit/commit/24c208d6a340c3071f28a9b5cce02b9377adfd43
 */
final class AllowMockObjectsWhereParentClassRector extends AbstractRector
{
    /**
     * @readonly
     */
    private TestsNodeAnalyzer $testsNodeAnalyzer;
    /**
     * @readonly
     */
    private AttributeFinder $attributeFinder;
    /**
     * @readonly
     */
    private ReflectionProvider $reflectionProvider;
    /**
     * @var string[]
     */
    private const PARENT_CLASSES = [PHPUnitClassName::SYMFONY_CONSTRAINT_VALIDATOR_TEST_CASE, PHPUnitClassName::SYMFONY_TYPE_TEST_CASE];
    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer, AttributeFinder $attributeFinder, ReflectionProvider $reflectionProvider)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
        $this->attributeFinder = $attributeFinder;
        $this->reflectionProvider = $reflectionProvider;
    }
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Class_
    {
        if ($this->shouldSkipClass($node)) {
            return null;
        }
        if (!$this->hasRelateParentClass($node)) {
            return null;
        }
        // add attribute
        $node->attrGroups[] = new AttributeGroup([new Attribute(new FullyQualified(PHPUnitAttribute::ALLOW_MOCK_OBJECTS_WITHOUT_EXPECTATIONS))]);
        return $node;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add #[AllowMockObjectsWithoutExpectations] attribute to PHPUnit test classes with a 3rd party test case, that provides any mocks', [new CodeSample(<<<'CODE_SAMPLE'
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

final class SomeTest extends ConstraintValidatorTestCase
{
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
final class SomeTest extends ConstraintValidatorTestCase
{
}
CODE_SAMPLE
)]);
    }
    private function shouldSkipClass(Class_ $class): bool
    {
        if (!$this->testsNodeAnalyzer->isInTestClass($class)) {
            return \true;
        }
        // attribute must exist for the rule to work
        if (!$this->reflectionProvider->hasClass(PHPUnitAttribute::ALLOW_MOCK_OBJECTS_WITHOUT_EXPECTATIONS)) {
            return \true;
        }
        // already filled
        return $this->attributeFinder->hasAttributeByClasses($class, [PHPUnitAttribute::ALLOW_MOCK_OBJECTS_WITHOUT_EXPECTATIONS]);
    }
    private function hasRelateParentClass(Class_ $class): bool
    {
        $scope = ScopeFetcher::fetch($class);
        $classReflection = $scope->getClassReflection();
        if (!$classReflection instanceof ClassReflection) {
            return \false;
        }
        foreach (self::PARENT_CLASSES as $parentClass) {
            if ($classReflection->is($parentClass)) {
                return \true;
            }
        }
        return \false;
    }
}
