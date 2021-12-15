<?php

namespace Rector\Tests\DowngradePhp81\Rector\FunctionLike\DowngradeNewInInitializerRector\Fixture;

use stdClass;

class NewInArray
{
    public function __construct(public array $property = [
        'a' => new stdClass()
    ])
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp81\Rector\FunctionLike\DowngradeNewInInitializerRector\Fixture;

use stdClass;

class NewInArray
{
    public function __construct(public ?array $property = null)
    {
        $this->property = $property ?? [
            'a' => new stdClass()
        ];
    }
}

?>
