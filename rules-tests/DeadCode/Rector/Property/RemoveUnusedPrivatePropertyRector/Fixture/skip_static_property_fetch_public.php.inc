<?php

declare(strict_types=1);

namespace Rector\Tests\DeadCode\Rector\Property\RemoveUnusedPrivatePropertyRector\Fixture;

class SkipStaticPropertyFetchPublic
{
    private string $entityId;
    private string $className;
    public static array $pendingList = [];
    public static array $retrievedList = [];

    public function __construct(DTO $dto)
    {
        $this->entityId = $dto->getId();
        $this->className = $dto::class;

        self::$pendingList[$this->className][$this->entityId] = true;
    }
}


