<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Nette\Helper;

use PhpParser\Node\Arg;

final class ServiceFromEnvironmentResolver
{
    public function resolveServiceClassFromArgument(Arg $argNode): string
    {
    }
}
