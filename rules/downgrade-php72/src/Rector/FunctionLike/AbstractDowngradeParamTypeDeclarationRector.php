<?php

declare(strict_types=1);

namespace Rector\DowngradePhp72\Rector\FunctionLike;

use PhpParser\Node\FunctionLike;
use PhpParser\Node\Param;
use Rector\DowngradePhp70\Rector\FunctionLike\AbstractDowngradeParamDeclarationRector;
use Rector\DowngradePhp72\Contract\Rector\DowngradeTypeRectorInterface;
use Rector\PHPStanStaticTypeMapper\Utils\TypeUnwrapper;

abstract class AbstractDowngradeParamTypeDeclarationRector extends AbstractDowngradeParamDeclarationRector implements DowngradeTypeRectorInterface
{
    /**
     * @var TypeUnwrapper
     */
    private $typeUnwrapper;

    public function __construct(TypeUnwrapper $typeUnwrapper)
    {
        $this->typeUnwrapper = $typeUnwrapper;
    }

    public function shouldRemoveParamDeclaration(Param $param, FunctionLike $functionLike): bool
    {
        if ($param->variadic) {
            return false;
        }

        if ($param->type === null) {
            return false;
        }

        $type = $this->staticTypeMapper->mapPhpParserNodePHPStanType($param->type);
        $type = $this->typeUnwrapper->unwrapNullableType($type);

        return is_a($type, $this->getTypeToRemove(), true);
    }

    protected function getRectorDefinitionDescription(): string
    {
        return sprintf("Remove the '%s' param type, add a @param tag instead", $this->getTypeToRemove());
    }
}
