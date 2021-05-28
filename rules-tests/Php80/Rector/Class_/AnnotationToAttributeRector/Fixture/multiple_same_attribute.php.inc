<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\FixtureAutoImported;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\ChangeTrackingPolicy;

/**
 * @Entity
 * @ChangeTrackingPolicy("DEFERRED_IMPLICIT")
 * @ChangeTrackingPolicy("NOTIFY")
 */
class User
{
}
?>
-----
<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\FixtureAutoImported;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\ChangeTrackingPolicy;

#[\Doctrine\ORM\Mapping\Entity]
#[\Doctrine\ORM\Mapping\ChangeTrackingPolicy('DEFERRED_IMPLICIT')]
#[\Doctrine\ORM\Mapping\ChangeTrackingPolicy('NOTIFY')]
class User
{
}
?>
