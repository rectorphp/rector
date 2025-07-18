<?php

declare (strict_types=1);
namespace Rector\Strict\Rector;

use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\Rector\AbstractRector;
use RectorPrefix202507\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Strict\Rector\BooleanNot\BooleanInBooleanNotRuleFixerRector\BooleanInBooleanNotRuleFixerRectorTest
 *
 * @internal
 */
abstract class AbstractFalsyScalarRuleFixerRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @api
     * @var string
     */
    public const TREAT_AS_NON_EMPTY = 'treat_as_non_empty';
    protected bool $treatAsNonEmpty = \false;
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
        $treatAsNonEmpty = $configuration[self::TREAT_AS_NON_EMPTY] ?? (bool) \current($configuration);
        Assert::boolean($treatAsNonEmpty);
        $this->treatAsNonEmpty = $treatAsNonEmpty;
    }
}
