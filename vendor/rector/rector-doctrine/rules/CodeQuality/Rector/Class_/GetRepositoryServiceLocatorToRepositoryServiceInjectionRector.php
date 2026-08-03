<?php

declare (strict_types=1);
namespace Rector\Doctrine\CodeQuality\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Rector\Exception\ShouldNotHappenException;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @deprecated as too project specific to be a generic rule. The repository class was resolved by reading the entity
 *             file contents with a regular expression, which is unreliable. Use a custom rule tailored to your
 *             project instead.
 */
final class GetRepositoryServiceLocatorToRepositoryServiceInjectionRector extends AbstractRector implements DeprecatedInterface
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Turns $this->entityManager->getRepository(...) on entity that supports service repository, to constructor injection', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public function run()
    {
        return $this->getRepository(SomeEntity::class)->find(1);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    public function __construct(
        private SomeEntityRepository $someEntityRepository
    ) {
    }

    public function run()
    {
        return $this->someEntityRepository->find(1);
    }
}
CODE_SAMPLE
)]);
    }
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        throw new ShouldNotHappenException(sprintf('"%s" is deprecated and should not be used anymore. Remove it from your config files.', self::class));
    }
}
