<?php

namespace Rector\Core\Tests\Issues\AliasedImportDouble\Fixture;

use Symfony\Component\Validator\Constraints as Assert;

final class CallbackWithCuryBraces
{
    /**
     * @Assert\Choice(callback={"Some\Random\Class_", "getChoices"})
     */
    private $attribute;
}

?>
-----
<?php

namespace Rector\Core\Tests\Issues\AliasedImportDouble\Fixture;

use Symfony\Component\Validator\Constraints as Assert;

final class CallbackWithCuryBraces
{
    /**
     * @Assert\Choice(callback={"Some\Other\Random\Class_", "getChoices"})
     */
    private $attribute;
}

?>
