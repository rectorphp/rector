<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Class_;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\TypeDeclaration\TypeInferer\SilentVoidResolver;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\Class_\AddTestsVoidReturnTypeWhereNoReturnRector\AddTestsVoidReturnTypeWhereNoReturnRectorTest
 */
final class AddTestsVoidReturnTypeWhereNoReturnRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer
     */
    private $testsNodeAnalyzer;
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\TypeInferer\SilentVoidResolver
     */
    private $silentVoidResolver;
    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer, SilentVoidResolver $silentVoidResolver)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
        $this->silentVoidResolver = $silentVoidResolver;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add void to PHPUnit test methods', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

class SomeClass extends TestCase
{
    public function testSomething()
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

class SomeClass extends TestCase
{
    public function testSomething(): void
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
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->testsNodeAnalyzer->isInTestClass($node)) {
            return null;
        }
        $hasChanged = \false;
        foreach ($node->getMethods() as $classMethod) {
            // has type already
            if ($classMethod->returnType instanceof Node) {
                continue;
            }
            if (!$this->testsNodeAnalyzer->isTestClassMethod($classMethod)) {
                continue;
            }
            if ($classMethod->isAbstract()) {
                continue;
            }
            if (!$this->silentVoidResolver->hasExclusiveVoid($classMethod)) {
                continue;
            }
            $classMethod->returnType = new Identifier('void');
            $hasChanged = \true;
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::VOID_TYPE;
    }
}
