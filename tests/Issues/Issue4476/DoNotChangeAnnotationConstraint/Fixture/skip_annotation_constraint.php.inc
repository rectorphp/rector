<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Issues\Issue4476\DoNotChangeAnnotationConstraint\Fixture;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="UserPasswordReset", uniqueConstraints={
 *      @ORM\UniqueConstraint(
 *          name="unique_reset", columns={
 *              "token",
 *              "bar"
 *          }
 *      )
 * })
 */
class UserPasswordReset {}

?>
