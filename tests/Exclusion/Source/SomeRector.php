<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Exclusion\Source;

use Rector\Core\Contract\Rector\RectorInterface;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class SomeRector implements RectorInterface
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('...' , []);
    }
}
