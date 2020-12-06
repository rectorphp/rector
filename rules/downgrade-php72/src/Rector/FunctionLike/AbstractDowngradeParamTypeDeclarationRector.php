<?php

declare(strict_types=1);

namespace Rector\DowngradePhp72\Rector\FunctionLike;

use PhpParser\Node\FunctionLike;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use Rector\DowngradePhp71\Rector\FunctionLike\AbstractDowngradeParamDeclarationRector;
use Rector\DowngradePhp72\Contract\Rector\DowngradeTypeRectorInterface;

abstract class AbstractDowngradeParamTypeDeclarationRector extends AbstractDowngradeParamDeclarationRector implements DowngradeTypeRectorInterface
{
    public function shouldRemoveParamDeclaration(Param $param, FunctionLike $functionLike): bool
    {
        if ($param->variadic) {
            return false;
        }

        if ($param->type === null) {
            return false;
        }

        $type = $this->staticTypeMapper->mapPhpParserNodePHPStanType($param->type);
        if ($type instanceof NullableType) {
            $type = $type->type;
        }

        return is_a($type, $this->getTypeToRemove(), true);
    }

    protected function getRectorDefinitionDescription(): string
    {
        return sprintf("Remove the '%s' param type, add a @param tag instead", $this->getTypeToRemove());
    }
}
