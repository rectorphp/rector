<?php

declare (strict_types=1);
namespace Rector\PHPUnit\PHPUnit120\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\MethodName;
use Rector\VersionBonding\Contract\ComposerPackageConstraintInterface;
use Rector\VersionBonding\ValueObject\ComposerPackageConstraint;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * The TestCase::__construct() was declared final in PHPUnit 12.0.3, overriding it is a fatal error since then
 *
 * @see \Rector\PHPUnit\Tests\PHPUnit120\Rector\Class_\RemoveOverrideFinalConstructTestCaseRector\RemoveOverrideFinalConstructTestCaseRectorTest
 *
 * @see https://github.com/sebastianbergmann/phpunit/commit/3263f4c62d4af44777ff1c81d093ee88eafccdad
 */
final class RemoveOverrideFinalConstructTestCaseRector extends AbstractRector implements ComposerPackageConstraintInterface
{
    /**
     * @readonly
     */
    private TestsNodeAnalyzer $testsNodeAnalyzer;
    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
    }
    public function provideComposerPackageConstraint(): ComposerPackageConstraint
    {
        return new ComposerPackageConstraint('phpunit/phpunit', '>=12.0.3');
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove override final construct test case', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeClass extends TestCase
{
    public function __construct()
    {
        parent::__construct(static::class);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeClass extends TestCase
{
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
    public function refactor(Node $node): ?\PhpParser\Node
    {
        if (!$this->testsNodeAnalyzer->isInTestClass($node)) {
            return null;
        }
        foreach ($node->stmts as $key => $stmt) {
            if (!$stmt instanceof ClassMethod) {
                continue;
            }
            if (!$this->isName($stmt, MethodName::CONSTRUCT)) {
                continue;
            }
            unset($node->stmts[$key]);
            return $node;
        }
        return null;
    }
}
