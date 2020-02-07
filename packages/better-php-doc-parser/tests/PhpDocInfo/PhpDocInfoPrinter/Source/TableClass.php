<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Tests\PhpDocInfo\PhpDocInfoPrinter\Source;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table("Table_Name")
 * @ORM\Entity()
 * @ORM\InheritanceType("SINGLE_TABLE")
 */
class TableClass
{
}
