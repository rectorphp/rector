<?php declare(strict_types=1);

namespace Rector\ReflectionDocBlock\NodeAnalyzer;

use Symplify\BetterPhpDocParser\Contract\PhpDocInfoDecoratorInterface;
use Symplify\BetterPhpDocParser\PhpDocParser\PhpDocInfo;

final class PhpDocInfoFqnTypeDecorator implements PhpDocInfoDecoratorInterface
{
    public function decorate(PhpDocInfo $phpDocInfo): void
    {
        dump('todo');
        die;

        // iterate all the nodes and make types FQN
    }
}
