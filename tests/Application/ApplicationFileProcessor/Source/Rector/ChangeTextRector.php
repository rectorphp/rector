<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Application\ApplicationFileProcessor\Source\Rector;

use Rector\Core\Tests\Application\ApplicationFileProcessor\Source\Contract\TextRectorInterface;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class ChangeTextRector implements TextRectorInterface
{
    public function refactorContent(string $content): string
    {
        return str_replace('Foo', 'Bar', $content);
    }

    public function getRuleDefinition(): RuleDefinition
    {
        // just for docs
    }
}
