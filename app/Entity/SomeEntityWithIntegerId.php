<?php declare(strict_types=1);

namespace Rector\App;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class SomeEntityWithIntegerId
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
}
