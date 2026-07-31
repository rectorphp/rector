<?php

declare (strict_types=1);
namespace Rector\TypeDeclarationDocblocks\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Rector\Exception\ShouldNotHappenException;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @deprecated This rule is deprecated, as it copies docblock from another method call. The docblock can be incorrect or outdated, and spreads the error further.
 */
final class AddReturnDocblockFromMethodCallDocblockRector extends AbstractRector implements DeprecatedInterface
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add @return docblock based on detailed type of method call docblock', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeController
{
    public function getAll(): array
    {
        return $this->repository->findAll();
    }
}

final class Repository
{
    /**
     * @return SomeEntity[]
     */
    public function findAll(): array
    {
        // ...
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeController
{
    /**
     * @return SomeEntity[]
     */
    public function getAll(): array
    {
        return $this->repository->findAll();
    }
}

final class Repository
{
    /**
     * @return SomeEntity[]
     */
    public function findAll(): array
    {
        // ...
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
        throw new ShouldNotHappenException(sprintf('"%s" is deprecated, as it copies docblock from another method call that can be incorrect or outdated', self::class));
    }
}
