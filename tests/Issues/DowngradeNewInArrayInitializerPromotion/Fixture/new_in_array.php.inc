<?php

namespace Rector\Core\Tests\Issues\DowngradeNewInArrayInitializerPromotion\Fixture;

use stdClass;

class NewInArray
{
    /**
     * @param array<string, object> $property
     */
    public function __construct(public array $property = [
        'a' => new stdClass()
    ])
    {
    }
}

?>
-----
<?php

namespace Rector\Core\Tests\Issues\DowngradeNewInArrayInitializerPromotion\Fixture;

use stdClass;

class NewInArray
{
    /**
     * @var array<string, object>
     */
    public $property;
    /**
     * @param array<string, object> $property
     */
    public function __construct(array $property = null)
    {
        $property ??= [
            'a' => new stdClass()
        ];
        $this->property = $property;
    }
}

?>
