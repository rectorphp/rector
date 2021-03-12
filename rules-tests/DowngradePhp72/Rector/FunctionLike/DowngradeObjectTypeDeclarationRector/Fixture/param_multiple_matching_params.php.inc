<?php

namespace Rector\Tests\DowngradePhp72\Rector\FunctionLike\DowngradeObjectTypeDeclarationRector\Fixture;

class ParamMultipleMatchingParams
{
    public function someFunction(object $someObject, string $someOtherVar, object $someOtherObject, ?object $nullableObject)
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp72\Rector\FunctionLike\DowngradeObjectTypeDeclarationRector\Fixture;

class ParamMultipleMatchingParams
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
