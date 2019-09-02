<?php declare(strict_types=1);

namespace Rector\TypeDeclaration\TypeInferer;

use PhpParser\Node\FunctionLike;
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
        foreach ($this->returnTypeInferers as $returnTypeInferer) {
            $types = $returnTypeInferer->inferFunctionLike($functionLike);
            if ($types !== []) {
                return $types;
            }
        }

        return [];
    }

    /**
     * @param string[] $excludedInferers
     * @return string[]
     */
    public function inferFunctionLikeWithExcludedInferers(FunctionLike $functionLike, array $excludedInferers): array
    {
        foreach ($this->returnTypeInferers as $returnTypeInferer) {
            foreach ($excludedInferers as $excludedInferer) {
                if (is_a($returnTypeInferer, $excludedInferer, true)) {
                    continue 2;
                }
            }

            $types = $returnTypeInferer->inferFunctionLike($functionLike);
            if ($types !== []) {
                return $types;
            }
        }

        return [];
    }
}
