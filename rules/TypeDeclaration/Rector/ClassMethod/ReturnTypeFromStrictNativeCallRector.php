<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use Rector\PHPStan\ScopeFetcher;
use Rector\Rector\AbstractRector;
use Rector\TypeDeclaration\NodeManipulator\AddReturnTypeFromStrictNativeCall;
use Rector\ValueObject\PhpVersion;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictNativeCallRector\ReturnTypeFromStrictNativeCallRectorTest
 */
final class ReturnTypeFromStrictNativeCallRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     */
    private AddReturnTypeFromStrictNativeCall $addReturnTypeFromStrictNativeCall;
    public function __construct(AddReturnTypeFromStrictNativeCall $addReturnTypeFromStrictNativeCall)
    {
        $this->addReturnTypeFromStrictNativeCall = $addReturnTypeFromStrictNativeCall;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add strict return type based native function or native method', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public function run()
    {
        return strlen('value');
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    public function run(): int
    {
        return strlen('value');
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
        return $this->addReturnTypeFromStrictNativeCall->add($node, $scope);
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersion::PHP_70;
    }
}
