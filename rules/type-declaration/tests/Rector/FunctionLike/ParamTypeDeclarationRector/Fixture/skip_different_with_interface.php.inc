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

final class SkipDifferentWithInterface implements IntermediateInterface
{
    /**
     * @param string $countryCode
     */
    public function __construct($countryCode)
    {
    }
}
?>
