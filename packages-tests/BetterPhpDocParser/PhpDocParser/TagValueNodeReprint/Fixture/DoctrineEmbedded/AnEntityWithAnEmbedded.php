<?php
declare(strict_types=1);

namespace Rector\Tests\BetterPhpDocParser\PhpDocParser\TagValueNodeReprint\Fixture\DoctrineEmbedded;

use Doctrine\ORM\Mapping as ORM;
use Rector\Tests\BetterPhpDocParser\PhpDocParser\TagValueNodeReprint\Source\Embeddable;

final class AnEntityWithAnEmbedded
{
    /**
     * @ORM\Embedded(class="Embeddable")
     */
    private $embedded;
}
