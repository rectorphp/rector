<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Fixture;

use Symfony\Component\Validator\Constraints as Assert;

final class AssertChoice
{
    /**
     * @var string
     * @Assert\Choice({"chalet", "apartment"})
     */
    protected $type;
}

?>
-----
<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Fixture;

use Symfony\Component\Validator\Constraints as Assert;

final class AssertChoice
{
    /**
     * @Assert\Choice({"chalet", "apartment"})
     */
    protected string $type;
}

?>
