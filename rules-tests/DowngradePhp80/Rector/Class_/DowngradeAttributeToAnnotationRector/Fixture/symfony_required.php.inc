<?php

namespace Rector\Tests\DowngradePhp80\Rector\Class_\DowngradeAttributeToAnnotationRector\Fixture;

use Symfony\Contracts\Service\Attribute\Required;

final class SymfonyRequired
{
    #[Required]
    public function action()
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp80\Rector\Class_\DowngradeAttributeToAnnotationRector\Fixture;

use Symfony\Contracts\Service\Attribute\Required;

final class SymfonyRequired
{
    /**
     * @required
     */
    public function action()
    {
    }
}

?>
