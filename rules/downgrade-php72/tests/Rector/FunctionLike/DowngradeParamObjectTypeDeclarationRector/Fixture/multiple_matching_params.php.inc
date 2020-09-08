<?php

namespace Rector\DowngradePhp72\Tests\Rector\FunctionLike\DowngradeParamObjectTypeDeclarationRector\Fixture;

class MultipleMatchingParams
{
    public function someFunction(object $someObject, string $someOtherVar, object $someOtherObject, ?object $nullableObject)
    {
    }
}

?>
-----
<?php

namespace Rector\DowngradePhp72\Tests\Rector\FunctionLike\DowngradeParamObjectTypeDeclarationRector\Fixture;

class MultipleMatchingParams
{
    /**
     * @param object $someObject
     * @param object $someOtherObject
     * @param object|null $nullableObject
     */
    public function someFunction($someObject, string $someOtherVar, $someOtherObject, $nullableObject)
    {
    }
}

?>
