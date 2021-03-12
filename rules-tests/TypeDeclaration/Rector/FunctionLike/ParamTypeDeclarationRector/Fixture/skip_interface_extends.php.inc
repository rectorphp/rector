<?php
namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ParamTypeDeclarationRector\Fixture;

interface ParentInterface
{
    /**
     * @param float|int|string $value
     */
    public function __construct($value);
}

interface IntermediateInterface extends ParentInterface
{
    /**
     * @param string $countryCode
     */
    public function __construct($countryCode);
}

final class SkipInterfaceExtends implements IntermediateInterface
{
    /**
     * @param string $countryCode
     */
    public function __construct($countryCode)
    {
    }
}
?>
