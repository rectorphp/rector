<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Fixture\Doctrine;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="user_account_role", uniqueConstraints={
 *     @ORM\UniqueConstraint(name="user_account_role_unique", columns={"user_account_id", "list_user_role_id"})
 * })
 */
class TableAndUniqueConstraints
{
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Fixture\Doctrine;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'user_account_role')]
#[ORM\UniqueConstraint(name: 'user_account_role_unique', columns: ['user_account_id', 'list_user_role_id'])]
class TableAndUniqueConstraints
{
}

?>
