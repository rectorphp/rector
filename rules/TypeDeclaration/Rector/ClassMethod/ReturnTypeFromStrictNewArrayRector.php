<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PHPStan\Analyser\Scope;
use Rector\PHPStan\ScopeFetcher;
use Rector\Rector\AbstractRector;
use Rector\TypeDeclaration\NodeAnalyzer\StrictReturnNewArrayResolver;
use Rector\ValueObject\PhpVersion;
use Rector\VendorLocker\NodeVendorLocker\ClassMethodReturnTypeOverrideGuard;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictNewArrayRector\ReturnTypeFromStrictNewArrayRectorTest
 */
final class ReturnTypeFromStrictNewArrayRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     */
    private ClassMethodReturnTypeOverrideGuard $classMethodReturnTypeOverrideGuard;
    /**
     * @readonly
     */
    private StrictReturnNewArrayResolver $strictReturnNewArrayResolver;
    public function __construct(ClassMethodReturnTypeOverrideGuard $classMethodReturnTypeOverrideGuard, StrictReturnNewArrayResolver $strictReturnNewArrayResolver)
    {
        $this->classMethodReturnTypeOverrideGuard = $classMethodReturnTypeOverrideGuard;
        $this->strictReturnNewArrayResolver = $strictReturnNewArrayResolver;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add strict return array type based on created empty array and returned', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public function run()
    {
        $values = [];

        return $values;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    public function run(): array
    {
        $values = [];

        return $values;
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
        return [ClassMethod::class, Function_::class];
    }
    /**
     * @param ClassMethod|Function_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $scope = ScopeFetcher::fetch($node);
        if ($this->shouldSkip($node, $scope)) {
            return null;
        }
        return $this->strictReturnNewArrayResolver->resolve($node);
    }
    public function provideMinPhpVersion(): int
    {
        return PhpVersion::PHP_70;
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_ $node
     */
    private function shouldSkip($node, Scope $scope): bool
    {
        if ($node->returnType instanceof Node) {
            return \true;
        }
        // private methods are handled by PrivateMethodReturnTypeFromStrictNewArrayRector
        if ($node instanceof ClassMethod && $node->isPrivate()) {
            return \true;
        }
        return $node instanceof ClassMethod && $this->classMethodReturnTypeOverrideGuard->shouldSkipClassMethod($node, $scope);
    }
}
