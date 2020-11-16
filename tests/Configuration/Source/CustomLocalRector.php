<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Configuration\Source;

use Rector\Core\Contract\Rector\RectorInterface;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class CustomLocalRector implements RectorInterface
{
    public function getRuleDefinition(): RuleDefinition
    {
        // TODO: Implement getDefinition() method.
    }
}
