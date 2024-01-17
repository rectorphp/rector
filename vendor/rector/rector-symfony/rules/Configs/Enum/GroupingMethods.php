<?php

declare (strict_types=1);
namespace Rector\Symfony\Configs\Enum;

final class GroupingMethods
{
    /**
     * @var array<string, string>
     */
    public const GROUPING_METHOD_NAME_TO_SPLIT = ['connections' => 'connection', 'entity_managers' => 'entityManager', 'mappings' => 'mapping'];
}
