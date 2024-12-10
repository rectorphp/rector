<?php

declare (strict_types=1);
namespace Rector\Rector;

use PhpParser\Node;
use Rector\Contract\Rector\ScopeAwareRectorInterface;
use Rector\Exception\ShouldNotHappenException;
/**
 * @deprecated This class is deprecated, as too granular.
 * Use \Rector\Rector\AbstractRector instead with help of \Rector\PHPStan\ScopeFetcher
 */
abstract class AbstractScopeAwareRector extends \Rector\Rector\AbstractRector implements ScopeAwareRectorInterface
{
    /**
     * Process Node of matched type with its PHPStan scope
     */
    public function refactor(Node $node)
    {
        throw new ShouldNotHappenException(\sprintf('The `Rector\\Rector\\AbstractScopeAwareRector` is removed, use `Rector\\Rector\\AbstractRector` instead, see upgrading guide %s', 'https://github.com/rectorphp/rector-src/blob/main/UPGRADING.md#1-abstractscopeawarerector-is-removed-use-abstractrector-instead'));
    }
}
