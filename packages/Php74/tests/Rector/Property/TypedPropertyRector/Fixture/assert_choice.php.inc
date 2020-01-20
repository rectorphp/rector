<?php

namespace Rector\Php74\Tests\Rector\Property\TypedPropertyRector\Fixture;

use Symfony\Component\Validator\Constraints as Assert;

class AssertChoice
{
    /**
     * @var string
     * @Assert\Choice({"chalet", "apartment"})
     */
    public $type;
}

?>
-----
<?php

namespace Rector\Php74\Tests\Rector\Property\TypedPropertyRector\Fixture;

use Symfony\Component\Validator\Constraints as Assert;

class AssertChoice
{
    /**
     * @Assert\Choice({"chalet", "apartment"})
     */
    public string $type;
}

?>
