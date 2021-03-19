<?php
namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ParamTypeDeclarationRector\Fixture;

interface ParentInterface
{
    /**
     * @param float|int|string $value
     */
    public function run($value);
}

interface IntermediateInterface extends ParentInterface
{
    /**
     * @param string $countryCode
     */
    public function run($countryCode);
}

final class SkipInterfaceExtends implements IntermediateInterface
{
    /**
     * @param string $countryCode
     */
    public function run(string $countryCode)
    {
    }
}
