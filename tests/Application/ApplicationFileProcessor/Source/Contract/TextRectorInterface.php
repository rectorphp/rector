<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Application\ApplicationFileProcessor\Source\Contract;

use Rector\Core\Contract\Rector\RectorInterface;

interface TextRectorInterface extends RectorInterface
{
    public function refactorContent(string $content): string;
}
