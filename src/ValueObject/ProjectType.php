<?php

declare(strict_types=1);

namespace Rector\Core\ValueObject;

final class ProjectType
{
    /**
     * @var string
     */
    public const OPEN_SOURCE = 'open-source';

    /**
     * @var string
     */
    public const PROPRIETARY = 'proprietary';

    /**
     * @var string
     */
    public const OPEN_SOURCE_UNDESCORED = 'open_source';
}
