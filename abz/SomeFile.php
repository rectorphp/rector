<?php declare(strict_types=1);

namespace CampApp\Model\Infrastructure\Security\Logger;

use CampApp\Model\Infrastructure\Security\Authorizator\Action;
use CampApp\Model\Infrastructure\Security\Authorizator\PrimitiveAction;
use Ds\Stack;
use Mangoweb\Authorization\Scope\AuthorizationScope;


final class MemoryApplicationPermissionLogger
{
    /** @var Stack<array{queriedAction:string,      requiredActions:array{queriedAction:string, isAllowed:bool}[],      isAllowed:bool, type:string}>
     */
    private $permissionChecks;

    /** @var Stack<array{
     *      queriedAction: string,
     *      isAllowed: bool
     * }>
     */
    private $requiredActionChecks;

    /**
     * @return array{queriedAction:string,      requiredActions:array{queriedAction:string, isAllowed:bool}[],      isAllowed:bool, type:string}|null
     */
    public function getLastPermissionCheck(): ?array
    {
        return $this->permissionChecks->peek();
    }
}
