<?php declare(strict_types=1);

namespace CampApp\Model\Infrastructure\Security\Logger;

final class MemoryApplicationPermissionLogger
{
    /**
     * @var array{
     *      type:string,
     * }
     */
    private $permissionChecks;
}
