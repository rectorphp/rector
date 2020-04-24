<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNode\Symfony;

use Nette\Utils\Strings;
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

    /**
     * @var string|null
     */
    private $name;

    /**
     * @var string|null
     */
    private $path;

    /**
     * @var string|null
     */
    private $host;

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
     * @var string|null
     */
    private $condition;

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
        ?string $host = null,
        array $requirements = [],
        ?string $condition = null,
        ?string $originalContent = null
    ) {
        $this->path = $path;
        $this->localizedPaths = $localizedPaths;

        $this->name = $name;
        $this->methods = $methods;
        $this->options = $options;
        $this->defaults = $defaults;
        $this->requirements = $requirements;
        $this->host = $host;
        $this->condition = $condition;

        // covers https://github.com/rectorphp/rector/issues/2994#issuecomment-598712339

        if ($originalContent !== null) {
            $this->resolveOriginalContentSpacingAndOrder($originalContent, 'path');

            // @todo use generic approach
            $matches = Strings::match($originalContent, '#requirements={(.*?)(?<separator>(=|:))(.*)}#');
            $this->requirementsKeyValueSeparator = $matches['separator'] ?? '=';

            $this->resolveOriginalContentSpacingAndOrder($originalContent, 'path');
        }
    }

    public function __toString(): string
    {
        $contentItems = [
            'path' => $this->printValueWithOptionalQuotes('path', $this->path, $this->localizedPaths),
        ];

        // required
        $contentItems['name'] = $this->printValueWithOptionalQuotes('name', $this->name);

        if ($this->methods !== []) {
            $contentItems['methods'] = $this->printArrayItem($this->methods, 'methods');
        }

        if ($this->options !== []) {
            $contentItems['options'] = $this->printArrayItem($this->options, 'options');
        }

        if ($this->defaults !== []) {
            $contentItems['defaults'] = $this->printArrayItem($this->defaults, 'defaults');
        }

        if ($this->host !== null) {
            $contentItems['host'] = sprintf('host="%s"', $this->host);
        }

        if ($this->condition !== null) {
            $contentItems['condition'] = sprintf('condition="%s"', $this->condition);
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
}
