<?php

declare(strict_types=1);

namespace Rector\Tests\BetterPhpDocParser\PhpDocParser\TagValueNodeReprint\Fixture;

use Doctrine\ORM\Mapping as ORM;

final class JoinTable
{
    /**
     * @ORM\JoinTable(name="page_template_area",
     *      joinColumns={@ORM\JoinColumn(name="template_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="area_id", referencedColumnName="id")}
     * )
     */
    public $name;
}

class Area
{
}

?>
-----
PhpParser\Node\Stmt\Property
-----
Doctrine\ORM\Mapping\JoinTable
