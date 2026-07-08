<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Rector\AbstractRector;
use Rector\TypeDeclaration\NodeAnalyzer\StrictReturnNewArrayResolver;
use Rector\ValueObject\PhpVersion;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\ClassMethod\PrivateMethodReturnTypeFromStrictNewArrayRector\PrivateMethodReturnTypeFromStrictNewArrayRectorTest
 */
final class PrivateMethodReturnTypeFromStrictNewArrayRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     */
    private StrictReturnNewArrayResolver $strictReturnNewArrayResolver;
    public function __construct(StrictReturnNewArrayResolver $strictReturnNewArrayResolver)
    {
        $this->strictReturnNewArrayResolver = $strictReturnNewArrayResolver;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add strict return array type to private methods based on created empty array and returned', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    private function run()
    {
        $values = [];

        return $values;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    private function run(): array
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
        return [ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }
        return $this->strictReturnNewArrayResolver->resolve($node);
    }
    public function provideMinPhpVersion(): int
    {
        return PhpVersion::PHP_70;
    }
    private function shouldSkip(ClassMethod $classMethod): bool
    {
        if (!$classMethod->isPrivate()) {
            return \true;
        }
        return $classMethod->returnType instanceof Node;
    }
}
