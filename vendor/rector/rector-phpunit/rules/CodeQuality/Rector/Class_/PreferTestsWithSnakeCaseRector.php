<?php

declare (strict_types=1);
namespace Rector\PHPUnit\CodeQuality\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\PHPUnit\Tests\CodeQuality\Rector\Class_\PreferTestsWithSnakeCaseRector\PreferTestsWithSnakeCaseRectorTest
 */
final class PreferTestsWithSnakeCaseRector extends AbstractRector
{
    /**
     * @readonly
     */
    private TestsNodeAnalyzer $testsNodeAnalyzer;
    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Changes PHPUnit test methods to snake case', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeClass extends TestCase
{
    public function testSomething()
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeClass extends TestCase
{
    public function test_something()
    {
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
        $hasChanged = \false;
        foreach ($node->getMethods() as $classMethod) {
            if (!$this->testsNodeAnalyzer->isTestClassMethod($classMethod)) {
                continue;
            }
            $currentName = $classMethod->name->toString();
            $newName = $this->toSnakeCase($currentName);
            if ($currentName === $newName) {
                continue;
            }
            // avoid name collision with an existing method
            if ($node->getMethod($newName) instanceof Node) {
                continue;
            }
            $classMethod->name = new Node\Identifier($newName);
            $hasChanged = \true;
        }
        if ($hasChanged === \false) {
            return null;
        }
        return $node;
    }
    private function toSnakeCase(string $value): string
    {
        if (ctype_lower($value)) {
            return $value;
        }
        $value = (string) preg_replace('/\s+/u', '', ucwords($value));
        $value = (string) preg_replace('/(.)(?=[A-Z])/u', '$1_', $value);
        return strtolower($value);
    }
}
