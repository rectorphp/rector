<?php

namespace Rector\Core\Tests\Issues\Issue7023\Fixture;

use Doctrine\ORM\Mapping as ORM;
use Acme\Entity\User;

/**
 * @ORM\Entity
 */
class Fixture
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

namespace Rector\Core\Tests\Issues\Issue7023\Fixture;

use Doctrine\ORM\Mapping as ORM;
use Acme\Entity\User;

/**
 * @ORM\Entity
 */
class Fixture
{

    /**
     * @ORM\ManyToOne(targetEntity="\Acme\Entity\User", inversedBy="images")
     * @ORM\JoinColumn(name="data_id", nullable=true)
     * @var \Acme\Entity\User|null
     */
    private $user;
}
?>
