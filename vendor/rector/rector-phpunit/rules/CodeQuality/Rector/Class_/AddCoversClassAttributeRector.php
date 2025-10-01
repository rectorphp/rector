<?php

declare (strict_types=1);
namespace Rector\PHPUnit\CodeQuality\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\AttributeGroup;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Reflection\ReflectionProvider;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\Php80\NodeAnalyzer\PhpAttributeAnalyzer;
use Rector\PhpAttribute\NodeFactory\PhpAttributeGroupFactory;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\PHPUnit\ValueObject\TestClassSuffixesConfig;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix202510\Webmozart\Assert\Assert;
use function array_filter;
use function array_merge;
use function count;
use function explode;
use function implode;
use function in_array;
use function preg_replace;
use function strtolower;
use function trim;
final class AddCoversClassAttributeRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @readonly
     */
    private ReflectionProvider $reflectionProvider;
    /**
     * @readonly
     */
    private PhpAttributeGroupFactory $phpAttributeGroupFactory;
    /**
     * @readonly
     */
    private PhpAttributeAnalyzer $phpAttributeAnalyzer;
    /**
     * @readonly
     */
    private TestsNodeAnalyzer $testsNodeAnalyzer;
    /**
     * @var string[]
     */
    private array $testClassSuffixes = ['Test', 'TestCase'];
    public function __construct(ReflectionProvider $reflectionProvider, PhpAttributeGroupFactory $phpAttributeGroupFactory, PhpAttributeAnalyzer $phpAttributeAnalyzer, TestsNodeAnalyzer $testsNodeAnalyzer)
    {
        $this->reflectionProvider = $reflectionProvider;
        $this->phpAttributeGroupFactory = $phpAttributeGroupFactory;
        $this->phpAttributeAnalyzer = $phpAttributeAnalyzer;
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Adds `#[CoversClass(...)]` attribute to test files guessing source class name.', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
class SomeService
{
}

use PHPUnit\Framework\TestCase;

class SomeServiceFunctionalTest extends TestCase
{
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeService
{
}

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(SomeService::class)]
class SomeServiceFunctionalTest extends TestCase
{
}
CODE_SAMPLE
, [new TestClassSuffixesConfig(['Test', 'TestCase', 'FunctionalTest', 'IntegrationTest'])])]);
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
        $className = $this->getName($node);
        if ($className === null) {
            return null;
        }
        if (!$this->testsNodeAnalyzer->isInTestClass($node)) {
            return null;
        }
        if ($this->phpAttributeAnalyzer->hasPhpAttributes($node, ['PHPUnit\Framework\Attributes\CoversNothing', 'PHPUnit\Framework\Attributes\CoversClass', 'PHPUnit\Framework\Attributes\CoversFunction'])) {
            return null;
        }
        $possibleTestClassNames = $this->resolveSourceClassNames($className);
        $matchingTestClassName = $this->matchExistingClassName($possibleTestClassNames);
        if (!is_string($matchingTestClassName)) {
            return null;
        }
        $coversAttributeGroup = $this->createAttributeGroup('\\' . $matchingTestClassName);
        $node->attrGroups = array_merge($node->attrGroups, [$coversAttributeGroup]);
        return $node;
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration): void
    {
        Assert::countBetween($configuration, 0, 1);
        if (isset($configuration[0])) {
            Assert::isInstanceOf($configuration[0], TestClassSuffixesConfig::class);
            $this->testClassSuffixes = $configuration[0]->getSuffixes();
        }
    }
    /**
     * @return string[]
     */
    private function resolveSourceClassNames(string $className): array
    {
        $classNameParts = explode('\\', $className);
        $partCount = count($classNameParts);
        // Sort suffixes by length (longest first) to ensure more specific patterns match first
        $sortedSuffixes = $this->testClassSuffixes;
        usort($sortedSuffixes, static fn(string $a, string $b): int => strlen($b) <=> strlen($a));
        $patterns = [];
        foreach ($sortedSuffixes as $sortedSuffix) {
            $patterns[] = '#' . preg_quote($sortedSuffix, '#') . '$#';
        }
        $classNameParts[$partCount - 1] = preg_replace($patterns, '', $classNameParts[$partCount - 1]);
        $possibleTestClassNames = [implode('\\', $classNameParts)];
        $partsWithoutTests = array_filter($classNameParts, static fn(?string $part): bool => $part === null ? \false : !in_array(strtolower($part), ['test', 'tests'], \true));
        $possibleTestClassNames[] = implode('\\', $partsWithoutTests);
        return $possibleTestClassNames;
    }
    /**
     * @param string[] $classNames
     */
    private function matchExistingClassName(array $classNames): ?string
    {
        foreach ($classNames as $className) {
            if (!$this->reflectionProvider->hasClass($className)) {
                continue;
            }
            $classReflection = $this->reflectionProvider->getClass($className);
            if ($classReflection->isInterface()) {
                continue;
            }
            if ($classReflection->isTrait()) {
                continue;
            }
            if ($classReflection->isAbstract()) {
                continue;
            }
            return $className;
        }
        return null;
    }
    private function createAttributeGroup(string $annotationValue): AttributeGroup
    {
        $attributeClass = 'PHPUnit\Framework\Attributes\CoversClass';
        $attributeValue = trim($annotationValue) . '::class';
        return $this->phpAttributeGroupFactory->createFromClassWithItems($attributeClass, [$attributeValue]);
    }
}
