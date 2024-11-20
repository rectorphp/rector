<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use Rector\PHPStan\ScopeFetcher;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\TypeDeclaration\NodeManipulator\AddNeverReturnType;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnNeverTypeRector\ReturnNeverTypeRectorTest
 */
final class ReturnNeverTypeRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     */
    private AddNeverReturnType $addNeverReturnType;
    /**
     * @readonly
     */
    private TestsNodeAnalyzer $testsNodeAnalyzer;
    public function __construct(AddNeverReturnType $addNeverReturnType, TestsNodeAnalyzer $testsNodeAnalyzer)
    {
        $this->addNeverReturnType = $addNeverReturnType;
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add "never" return-type for methods that never return anything', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public function run()
    {
        throw new InvalidException();
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    public function run(): never
    {
        throw new InvalidException();
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
        return [ClassMethod::class, Function_::class];
    }
    /**
     * @param ClassMethod|Function_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        $scope = ScopeFetcher::fetch($node);
        if ($this->isTestClassMethodWithFilledReturnType($node)) {
            return null;
        }
        return $this->addNeverReturnType->add($node, $scope);
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::NEVER_TYPE;
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_ $callLike
     */
    private function isTestClassMethodWithFilledReturnType($callLike) : bool
    {
        if (!$callLike instanceof ClassMethod) {
            return \false;
        }
        if (!$callLike->isPublic()) {
            return \false;
        }
        if (!$this->testsNodeAnalyzer->isInTestClass($callLike)) {
            return \false;
        }
        return $callLike->returnType instanceof Node;
    }
}
