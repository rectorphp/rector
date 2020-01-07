<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNode\Symfony;

use Nette\Utils\Strings;
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
     * @var string|null
     */
    private $path;

    /**
     * @var string[]
     */
    private $methods = [];

    /**
     * @var bool
     */
    private $isPathExplicit = true;

    /**
     * @var mixed[]
     */
    private $options = [];

    /**
     * @var mixed[]
     */
    private $defaults = [];

    /**
     * @var mixed[]
     */
    private $requirements = [];

    /**
     * @param string[] $methods
     * @param string[] $options
     * @param string[] $defaults
     * @param string[] $requirements
     */
    public function __construct(
        ?string $path,
        ?string $name = null,
        array $methods = [],
        array $options = [],
        array $defaults = [],
        array $requirements = [],
        ?string $originalContent = null
    ) {
        $this->path = $path;
        $this->name = $name;
        $this->methods = $methods;
        $this->options = $options;
        $this->defaults = $defaults;
        $this->requirements = $requirements;

        if ($originalContent !== null) {
            $this->isPathExplicit = (bool) Strings::contains($originalContent, 'path=');

            $this->resolveOriginalContentSpacingAndOrder($originalContent);

            // default value without key
            if ($this->path && ! in_array('path', (array) $this->orderedVisibleItems, true)) {
                // add path as first item
                $this->orderedVisibleItems = array_merge(['path'], (array) $this->orderedVisibleItems);
            }
        }
    }

    public function __toString(): string
    {
        $contentItems = [
            'path' => $this->createPath(),
        ];

        if ($this->name) {
            $contentItems['name'] = sprintf('name="%s"', $this->name);
        }

        if ($this->methods !== []) {
            $contentItems['methods'] = $this->printArrayItem($this->methods, 'methods');
        }

        if ($this->options !== []) {
            $contentItems['options'] = $this->printArrayItem($this->options, 'options');
        }

        if ($this->defaults !== []) {
            $contentItems['defaults'] = $this->printArrayItem($this->defaults, 'defaults');
        }

        if ($this->requirements !== []) {
            $contentItems['requirements'] = $this->printArrayItem($this->requirements, 'requirements');
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

    private function createPath(): string
    {
        if ($this->isPathExplicit) {
            return sprintf('path="%s"', $this->path);
        }

        return sprintf('"%s"', $this->path);
    }
}
