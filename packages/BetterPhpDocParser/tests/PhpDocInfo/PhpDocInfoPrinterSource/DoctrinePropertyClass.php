<?php declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Tests\PhpDocInfo\PhpDocInfoPrinterSource;

use Doctrine\ORM\Mapping as ORM;

final class DoctrinePropertyClass
{
    /**
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Group")
     * @ORM\JoinTable(name="fos_user_user_group",
     *     joinColumns={@ORM\JoinColumn(referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(referencedColumnName="id")}
     * )
     */
    public $someProperty;
}
