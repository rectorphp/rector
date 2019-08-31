<?php declare(strict_types=1);

namespace Rector\TypeDeclaration\TypeInferer;

use PhpParser\Node\Param;
use Rector\TypeDeclaration\Contract\TypeInferer\ParamTypeInfererInterface;

final class ParamTypeInferer
{
    /**
     * @var ParamTypeInfererInterface[]
     */
    private $paramTypeInferers = [];

    /**
     * @param ParamTypeInfererInterface[] $paramTypeInferers
     */
    public function __construct(array $paramTypeInferers)
    {
        $this->paramTypeInferers = $paramTypeInferers;
    }

    /**
     * @return string[]
     */
    public function inferParam(Param $param): array
    {
        foreach ($this->paramTypeInferers as $paramTypeInferers) {
            $types = $paramTypeInferers->inferParam($param);
            if ($types !== []) {
                return $types;
            }
        }

        return [];
    }
}
