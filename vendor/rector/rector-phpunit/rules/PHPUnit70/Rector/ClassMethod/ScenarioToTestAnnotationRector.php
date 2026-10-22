<?php

declare (strict_types=1);
namespace Rector\PHPUnit\PHPUnit70\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Comments\NodeDocBlock\DocBlockUpdater;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockTagReplacer;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\RuleDoc\CodeSample\CodeSample;
use Rector\RuleDoc\RuleDefinition;
use Rector\VersionBonding\Contract\ComposerPackageConstraintInterface;
use Rector\VersionBonding\ValueObject\ComposerPackageConstraint;
/**
 * @see \Rector\PHPUnit\Tests\PHPUnit70\Rector\ClassMethod\ScenarioToTestAnnotationRector\ScenarioToTestAnnotationRectorTest
 */
final class ScenarioToTestAnnotationRector extends AbstractRector implements ComposerPackageConstraintInterface
{
    /**
     * @readonly
     */
    private DocBlockTagReplacer $docBlockTagReplacer;
    /**
     * @readonly
     */
    private DocBlockUpdater $docBlockUpdater;
    /**
     * @readonly
     */
    private PhpDocInfoFactory $phpDocInfoFactory;
    /**
     * @readonly
     */
    private TestsNodeAnalyzer $testsNodeAnalyzer;
    public function __construct(DocBlockTagReplacer $docBlockTagReplacer, DocBlockUpdater $docBlockUpdater, PhpDocInfoFactory $phpDocInfoFactory, TestsNodeAnalyzer $testsNodeAnalyzer)
    {
        $this->docBlockTagReplacer = $docBlockTagReplacer;
        $this->docBlockUpdater = $docBlockUpdater;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
    }
    /**
     * the "@scenario" annotation was dropped in PHPUnit 7.0
     */
    public function provideComposerPackageConstraint(): ComposerPackageConstraint
    {
        return new ComposerPackageConstraint('phpunit/phpunit', '>=7.0');
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change `@scenario` annotation to `@test` in a test class', [new CodeSample(<<<'CODE_SAMPLE'
/**
 * @scenario
 */
public function someMethod()
{
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
/**
 * @test
 */
public function someMethod()
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
        $phpDocInfo = $this->phpDocInfoFactory->createFromNode($node);
        if (!$phpDocInfo instanceof PhpDocInfo) {
            return null;
        }
        if (!$this->docBlockTagReplacer->replaceTagByAnother($phpDocInfo, 'scenario', 'test')) {
            return null;
        }
        $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($node);
        return $node;
    }
}
