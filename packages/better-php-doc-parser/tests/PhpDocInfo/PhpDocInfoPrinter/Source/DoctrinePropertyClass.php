<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Tests\PhpDocInfo\PhpDocInfoPrinter\Source;

use Doctrine\ORM\Mapping as ORM;

final class DoctrinePropertyClass
{
    /**
     * @ORM\JoinTable(name="fos_user_user_group",
     *     joinColumns={@ORM\JoinColumn(referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(referencedColumnName="id")}
     * )
     */
    public $someProperty;
}
