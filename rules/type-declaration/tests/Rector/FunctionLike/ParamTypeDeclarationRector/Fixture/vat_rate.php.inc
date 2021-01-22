<?php
namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ParamTypeDeclarationRector\Fixture;

interface SimpleValueObjectInterface
{
    /**
     * @param float|int|string $value
     */
    public function __construct($value);
}

interface VatRateInterface extends SimpleValueObjectInterface
{
    /**
     * @param string $countryCode
     */
    public function __construct($countryCode);
}

final class SkipDifferentWithInterface implements VatRateInterface
{
    /**
     * @param string $countryCode
     */
    public function __construct($countryCode)
    {
    }
}
?>
