<?php

declare(strict_types=1);

namespace Rector\Php80\Rector\If_;

use PhpParser\Node;
use PhpParser\Node\Stmt\If_;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see https://wiki.php.net/rfc/nullsafe_operator
 * @see \Rector\Php80\Tests\Rector\If_\NullsafeOperatorRector\NullsafeOperatorRectorTest
 */
final class NullsafeOperatorRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change if null check with nullsafe operator ?-> with full short circuiting', [
            new CodeSample(
                <<<'CODE_SAMPLE'
$country =  null;

if ($session !== null) {
    $user = $session->user;

    if ($user !== null) {
        $address = $user->getAddress();

        if ($address !== null) {
            $country = $address->country;
        }
    }
}
CODE_SAMPLE
,
                <<<'CODE_SAMPLE'
$country =  null;

$country = $session?->user?->getAddress()?->country;
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [If_::class];
    }

    /**
     * @param If_ $node
     */
    public function refactor(Node $node): ?Node
    {
        return $node;
    }
}
