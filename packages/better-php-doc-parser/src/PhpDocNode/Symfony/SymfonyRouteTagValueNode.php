<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNode\Symfony;

use Rector\BetterPhpDocParser\Contract\PhpDocNode\ShortNameAwareTagInterface;
use Rector\BetterPhpDocParser\PhpDocNode\AbstractTagValueNode;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @see \Rector\BetterPhpDocParser\Tests\PhpDocParser\TagValueNodeReprint\TagValueNodeReprintTest
 */
final class SymfonyRouteTagValueNode extends AbstractTagValueNode implements ShortNameAwareTagInterface
{
    /**
     * @var string
     */
    public const CLASS_NAME = Route::class;

    public function __construct(array $items, ?string $originalContent = null)
    {
        $this->items = $items;

        // covers https://github.com/rectorphp/rector/issues/2994#issuecomment-598712339

        $this->resolveOriginalContentSpacingAndOrder($originalContent, 'path');
    }

    public function __toString(): string
    {
        if (isset($this->items['path']) || isset($this->items['localizedPaths'])) {
            $this->items['path'] = $this->items['path'] ?? $this->items['localizedPaths'];
        }

        $items = $this->completeItemsQuotes($this->items);
        $items = $this->makeKeysExplicit($items);

        return $this->printContentItems($items);
    }

    public static function createFromAnnotationAndAnnotatoinContent(Route $route, string $originalContent)
    {
        $items = get_object_vars($route);
        return new self($items, $originalContent);
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
}
