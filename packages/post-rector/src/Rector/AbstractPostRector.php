<?php

declare(strict_types=1);

namespace Rector\PostRector\Rector;

use PhpParser\NodeVisitorAbstract;
use Rector\Core\Rector\AbstractRector\NameResolverTrait;
use Rector\PostRector\Contract\Rector\PostRectorInterface;

abstract class AbstractPostRector extends NodeVisitorAbstract implements PostRectorInterface
{
    use NameResolverTrait;
}
