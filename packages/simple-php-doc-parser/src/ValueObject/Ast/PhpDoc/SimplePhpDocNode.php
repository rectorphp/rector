<?php

declare(strict_types=1);

namespace Rector\SimplePhpDocParser\ValueObject\Ast\PhpDoc;

use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;

/**
 * @notfinal on purpose, so it can be extended by 3rd party
 */
class SimplePhpDocNode extends PhpDocNode
{
    public function getParam(string $desiredParamName): ?ParamTagValueNode
    {
        foreach ($this->getParamTagValues() as $paramTagValueNode) {
            $desiredParamNameWithDollar = '$' . ltrim($desiredParamName, '$');
            if ($paramTagValueNode->parameterName !== $desiredParamNameWithDollar) {
                continue;
            }

            return $paramTagValueNode;
        }

        return null;
    }

    public function getParamType(string $desiredParamName): ?TypeNode
    {
        $paramTagValueNode = $this->getParam($desiredParamName);
        if ($paramTagValueNode === null) {
            return null;
        }

        return $paramTagValueNode->type;
    }
}
