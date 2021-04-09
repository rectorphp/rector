<?php

declare(strict_types=1);

namespace Rector\ChangesReporting\ValueObject;

use Rector\Core\Contract\Rector\RectorInterface;
use ReflectionClass;

final class RectorWithFileAndLineChange
{
    /**
     * @var string
     */
    private $realPath;

    /**
     * @var int
     */
    private $line;

    /**
     * @var RectorInterface
     */
    private $rector;

    public function __construct(RectorInterface $rector, string $realPath, int $line)
    {
        $this->rector = $rector;
        $this->line = $line;
        $this->realPath = $realPath;
    }

    public function getRectorDefinitionsDescription(): string
    {
        $ruleDefinition = $this->rector->getRuleDefinition();
        return $ruleDefinition->getDescription();
    }

    public function getRectorClass(): string
    {
        return get_class($this->rector);
    }

    public function getRectorClassWithChangelogUrl(): string
    {
        $rectorClass = get_class($this->rector);

        $rectorReflection = new ReflectionClass($rectorClass);

        $docComment = $rectorReflection->getDocComment();

        if (! is_string($docComment)) {
            return $rectorClass;
        }

        $pattern = "#@link\s*(?<url>[a-zA-Z0-9, ()_].*)#";
        preg_match($pattern, $docComment, $matches);

        if (! array_key_exists('url', $matches)) {
            return $rectorClass;
        }

        if (! filter_var($matches['url'], FILTER_VALIDATE_URL)) {
            return $rectorClass;
        }

        return sprintf('%s (%s)', $rectorClass, trim((string) $matches['url']));
    }

    public function getLine(): int
    {
        return $this->line;
    }

    public function getRealPath(): string
    {
        return $this->realPath;
    }
}
