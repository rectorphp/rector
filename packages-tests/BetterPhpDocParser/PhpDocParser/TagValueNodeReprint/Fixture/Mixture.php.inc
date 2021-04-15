<?php
declare(strict_types=1);

namespace Rector\Tests\BetterPhpDocParser\PhpDocParser\TagValueNodeReprint\Fixture;

use Doctrine\ORM\Mapping as ORM;
use Swagger\Annotations as SWG;

/**
 * Long running task to track progress.
 *
 * @ORM\Entity(repositoryClass="Application\Api\Admin\Repository\AdminTaskRepository")
 * @ORM\Table(
 *     name="admin_task",
 *     options={"collate": "utf8_bin", "charset": "utf8"},
 * )
 *
 * @SWG\Definition(required={"id", "created", "modified", "status", "progress", "completed"})
 */
final class Mixture
{
}

?>
-----
PhpParser\Node\Stmt\Class_
-----
Swagger\Annotations\Definition
