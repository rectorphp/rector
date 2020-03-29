<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNode\Symfony;

use Nette\Utils\Strings;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\ShortNameAwareTagInterface;
use Rector\BetterPhpDocParser\PhpDocNode\AbstractTagValueNode;
use Symfony\Component\Routing\Annotation\Route;

final class SymfonyRouteTagValueNode extends AbstractTagValueNode implements ShortNameAwareTagInterface
{
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
     * @var bool
     */
    private $isPathExplicit = true;

    /**
     * @var string[]
     */
    private $methods = [];

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
     * @var string
     */
    private $requirementsKeyValueSeparator = '=';

    /**
     * @var string[]
     */
    private $localizedPaths = [];

    /**
     * @param string[] $localizedPaths
     * @param string[] $methods
     * @param string[] $options
     * @param string[] $defaults
     * @param string[] $requirements
     */
    public function __construct(
        ?string $path,
        array $localizedPaths = [],
        ?string $name = null,
        array $methods = [],
        array $options = [],
        array $defaults = [],
        array $requirements = [],
        ?string $originalContent = null
    ) {
        $this->path = $path;
        $this->localizedPaths = $localizedPaths;

        $this->name = $name;
        $this->methods = $methods;
        $this->options = $options;
        $this->defaults = $defaults;
        $this->requirements = $requirements;

        // covers https://github.com/rectorphp/rector/issues/2994#issuecomment-598712339

        if ($originalContent !== null) {
            $this->isPathExplicit = (bool) Strings::contains($originalContent, 'path=');

            $this->resolveOriginalContentSpacingAndOrder($originalContent);

            // default value without key
            if ($this->shouldAddIimplicitPaths()) {
                // add path as first item
                $this->orderedVisibleItems = array_merge(['path'], (array) $this->orderedVisibleItems);
            }

            $matches = Strings::match($originalContent, '#requirements={(.*?)(?<separator>(=|:))(.*)}#');
            $this->requirementsKeyValueSeparator = $matches['separator'] ?? '=';
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
            $contentItems['requirements'] = $this->printArrayItemWithSeparator(
                $this->requirements,
                'requirements',
                $this->requirementsKeyValueSeparator
            );
        }

        return $this->printContentItems($contentItems);
    }

    public function changeMethods(array $methods): void
    {
        $this->orderedVisibleItems[] = 'methods';
        $this->methods = $methods;
    }

    public function getShortName(): string
    {
        return '@Route';
    }

    private function createPath(): string
    {
        if ($this->isPathExplicit) {
            return sprintf('path="%s"', $this->path);
        }

        if ($this->path !== null) {
            return sprintf('"%s"', $this->path);
        }

        $localizedPaths = $this->printArrayItem($this->localizedPaths);

        return Strings::replace($localizedPaths, '#:#', ': ');
    }

    private function shouldAddIimplicitPaths(): bool
    {
        return ($this->path || $this->localizedPaths) && ! in_array('path', (array) $this->orderedVisibleItems, true);
    }
}
