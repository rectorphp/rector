<?php declare(strict_types=1);

namespace Rector\TypeDeclaration\TypeInferer;

use PhpParser\Node\FunctionLike;
use Rector\Exception\ShouldNotHappenException;
use Rector\TypeDeclaration\Contract\TypeInferer\ReturnTypeInfererInterface;

final class ReturnTypeInferer extends AbstractPriorityAwareTypeInferer
{
    /**
     * @var ReturnTypeInfererInterface[]
     */
    private $returnTypeInferers = [];

    /**
     * @param ReturnTypeInfererInterface[] $returnTypeInferers
     */
    public function __construct(array $returnTypeInferers)
    {
        $this->returnTypeInferers = $this->sortTypeInferersByPriority($returnTypeInferers);
    }

    /**
     * @return string[]
     */
    public function inferFunctionLike(FunctionLike $functionLike): array
    {
        return $this->inferFunctionLikeWithExcludedInferers($functionLike, []);
    }

    /**
     * @param string[] $excludedInferers
     * @return string[]
     */
    public function inferFunctionLikeWithExcludedInferers(FunctionLike $functionLike, array $excludedInferers): array
    {
        foreach ($this->returnTypeInferers as $returnTypeInferer) {
            if ($this->shouldSkipExcludedTypeInferer($returnTypeInferer, $excludedInferers)) {
                continue;
            }

            $types = $returnTypeInferer->inferFunctionLike($functionLike);
            if ($types !== [] && $types !== ['mixed']) {
                return $types;
            }
        }

        return [];
    }

    /**
     * @param string[] $excludedInferers
     */
    private function shouldSkipExcludedTypeInferer(
        ReturnTypeInfererInterface $returnTypeInferer,
        array $excludedInferers
    ): bool {
        foreach ($excludedInferers as $excludedInferer) {
            $this->ensureIsTypeInferer($excludedInferer);

            if (is_a($returnTypeInferer, $excludedInferer)) {
                return true;
            }
        }

        return false;
    }

    private function ensureIsTypeInferer(string $excludedInferer): void
    {
        if (is_a($excludedInferer, ReturnTypeInfererInterface::class, true)) {
            return;
        }

        throw new ShouldNotHappenException();
    }
}
