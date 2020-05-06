<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNode\Symfony;

use Rector\BetterPhpDocParser\Contract\PhpDocNode\ShortNameAwareTagInterface;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\SilentKeyNodeInterface;
use Rector\BetterPhpDocParser\PhpDocNode\AbstractTagValueNode;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @see \Rector\BetterPhpDocParser\Tests\PhpDocParser\TagValueNodeReprint\TagValueNodeReprintTest
 */
final class SymfonyRouteTagValueNode extends AbstractTagValueNode implements ShortNameAwareTagInterface, SilentKeyNodeInterface
{
    /**
     * @var string
     */
    public const CLASS_NAME = Route::class;

    public function __toString(): string
    {
        $items = $this->items;
        if (isset($items['path']) || isset($items['localizedPaths'])) {
            $items['path'] = $items['path'] ?? $this->items['localizedPaths'];
        }

        return $this->printItems($items);
    }

    public function changeMethods(array $methods): void
    {
        $this->orderedVisibleItems[] = 'methods';
        $this->items['methods'] = $methods;
    }

    public function getShortName(): string
    {
        return '@Route';
    }

    public function getSilentKey(): string
    {
        return 'path';
    }
}
