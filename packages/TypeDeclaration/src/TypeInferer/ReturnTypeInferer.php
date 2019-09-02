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
        foreach ($this->returnTypeInferers as $returnTypeInferers) {
            $types = $returnTypeInferers->inferFunctionLike($functionLike);
            if ($types !== []) {
                return $types;
            }
        }

        return [];
    }
}
