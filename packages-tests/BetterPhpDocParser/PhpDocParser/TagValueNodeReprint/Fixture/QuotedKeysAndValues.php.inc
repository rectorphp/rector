<?php
declare(strict_types=1);

namespace Rector\Tests\BetterPhpDocParser\PhpDocParser\TagValueNodeReprint\Fixture;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Entity\BaseRepository")
 *
 * @Api\ApiResource(
 *     normalizationContext={"groups"={"default"}},
 *     denormalizationContext={"groups"={"write"}},
 *     collectionOperations={"get"},
 *     itemOperations={"get"},
 *     order={"code"},
 *     paginationEnabled=false
 * )
 */
final class QuotedKeysAndValues
{
}

?>
-----
PhpParser\Node\Stmt\Class_
-----
Doctrine\ORM\Mapping\Entity
