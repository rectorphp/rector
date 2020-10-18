<?php

namespace Rector\DeadCode\Tests\Rector\Property\RemoveUnusedPrivatePropertyRector\Fixture;

use Throwable;

final class SkipArgumentPass
{
    /**
     * @var string
     */
    private $exceptionKey;

    /**
     * @param null|string[] $keys
     */
    public function __construct(?string $exceptionKey = null)
    {
        $this->exceptionKey = $exceptionKey ?? 'exception';
    }

    public function buildData(Throwable $throwable, array $data): array
    {
        $data[$this->exceptionKey] = $throwable;
        $this->buildData($throwable, $data);
    }
}
