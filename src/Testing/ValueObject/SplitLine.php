<?php

declare(strict_types=1);

namespace Rector\Core\Testing\ValueObject;

final class SplitLine
{
    /**
     * @var string
     */
    public const REGEX = "#-----\r?\n#";

    /**
     * @var string
     */
    public const LINE = '-----' . PHP_EOL;
}
