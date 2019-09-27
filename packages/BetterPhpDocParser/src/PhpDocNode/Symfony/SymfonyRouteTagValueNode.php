<?php declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNode\Symfony;

use Rector\BetterPhpDocParser\PhpDocNode\AbstractTagValueNode;
use Symfony\Component\Routing\Annotation\Route;

final class SymfonyRouteTagValueNode extends AbstractTagValueNode
{
    /**
     * @var string
     */
    public const SHORT_NAME = '@Route';

    /**
     * @var string
     */
    public const CLASS_NAME = Route::class;

    /**
     * @var string|null
     */
    public $name;

    /**
     * @var string
     */
    private $path;

    /**
     * @var string[]
     */
    private $methods = [];

    /**
     * @param string[] $methods
     */
    public function __construct(
        string $path,
        ?string $name = null,
        array $methods = [],
        ?string $originalContent = null
    ) {
        $this->path = $path;
        $this->name = $name;
        $this->methods = $methods;

        if ($originalContent !== null) {
            $this->resolveOriginalContentSpacingAndOrder($originalContent);

            // default value without key
            if ($this->path && ! in_array('path', (array) $this->orderedVisibleItems, true)) {
                $this->orderedVisibleItems[] = 'path';
            }
        }
    }

    public function __toString(): string
    {
        $contentItems = [
            'path' => sprintf('path="%s"', $this->path),
        ];

        if ($this->name) {
            $contentItems['name'] = sprintf('name="%s"', $this->name);
        }

        if ($this->methods) {
            $contentItems['methods'] = $this->printArrayItem($this->methods, 'methods');
        }

        return $this->printContentItems($contentItems);
    }

    /**
     * @param mixed[] $methods
     */
    public function changeMethods(array $methods): void
    {
        $this->orderedVisibleItems[] = 'methods';
        $this->methods = $methods;
    }
}
