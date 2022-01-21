<?php

namespace Rector\Core\Tests\Issues\ConstructorPromoAnnotationToAttribute\Fixture;

use OpenApi\Annotations as OA;

class TestResponse
{
    /**
     * @OA\Property(
     *     type="array",
     *     @OA\Items(ref=@Model(type=TestItem::class))
     * )
     */
    public array $items;

    public function __construct(array $items = [])
    {
        $this->items = $items;
    }
}
?>
-----
<?php

namespace Rector\Core\Tests\Issues\ConstructorPromoAnnotationToAttribute\Fixture;

use OpenApi\Annotations as OA;

class TestResponse
{
    public function __construct(
        /**
         * @OA\Property(
         *     type="array",
         *     @OA\Items(ref=@Model(type=TestItem::class))
         * )
         */
        public array $items = []
    )
    {
    }
}
?>
