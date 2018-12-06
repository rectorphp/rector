<?php declare(strict_types=1);

namespace Rector\Util;

use Symfony\Component\Console\Input\StringInput;
use Symplify\PackageBuilder\Reflection\PrivatesCaller;

final class RectorStrings
{
    /**
     * @return string[]
     */
    public static function splitCommandToItems(string $command): array
    {
        $privatesCaller = new PrivatesCaller();

        return $privatesCaller->callPrivateMethod(new StringInput(''), 'tokenize', $command);
    }
}
