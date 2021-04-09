<?php
declare(strict_types=1);


namespace Rector\Tests\ChangesReporting\ValueObject\Source;

use Rector\Core\Contract\Rector\RectorInterface;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Class RectorWithLink
 * @link https://github.com/rectorphp/rector/blob/master/docs/rector_rules_overview.md
 */
final class RectorWithLink implements RectorInterface
{

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Foo', []);
    }
}
