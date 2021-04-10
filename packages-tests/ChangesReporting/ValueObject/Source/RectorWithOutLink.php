<?php
declare(strict_types=1);


namespace Rector\Tests\ChangesReporting\ValueObject\Source;

use Rector\Core\Contract\Rector\RectorInterface;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class RectorWithOutLink implements RectorInterface
{

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Foo', []);
    }
}
