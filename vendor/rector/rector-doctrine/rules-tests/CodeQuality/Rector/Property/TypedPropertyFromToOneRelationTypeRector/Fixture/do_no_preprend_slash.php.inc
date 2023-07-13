<?php

namespace Rector\Doctrine\Tests\CodeQuality\Rector\Property\TypedPropertyFromToOneRelationTypeRector\Fixture;


use Doctrine\ORM\Mapping as ORM;
use Acme\Entity\User;

/**
 * @ORM\Entity
 */
class DoNotPrependSlash
{

    /**
     * @ORM\ManyToOne(targetEntity="\Acme\Entity\User", inversedBy="images")
     * @ORM\JoinColumn(name="data_id", nullable=true)
     * @var User
     */
    private $user;
}

?>
-----
<?php

namespace Rector\Doctrine\Tests\CodeQuality\Rector\Property\TypedPropertyFromToOneRelationTypeRector\Fixture;


use Doctrine\ORM\Mapping as ORM;
use Acme\Entity\User;

/**
 * @ORM\Entity
 */
class DoNotPrependSlash
{

    /**
     * @ORM\ManyToOne(targetEntity="\Acme\Entity\User", inversedBy="images")
     * @ORM\JoinColumn(name="data_id", nullable=true)
     * @var User
     */
    private ?\Acme\Entity\User $user = null;
}

?>
