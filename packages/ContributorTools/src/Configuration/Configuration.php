<?php declare(strict_types=1);

namespace Rector\ContributorTools\Configuration;

use Nette\Utils\Strings;

final class Configuration
{
    /**
     * @var string
     */
    private $package;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string[]
     */
    private $nodeTypes = [];

    /**
     * @var string
     */
    private $category;

    /**
     * @var string
     */
    private $codeBefore;

    /**
     * @var string
     */
    private $codeAfter;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $source;

    /**
     * @var string|null
     */
    private $levelConfig;

    /**
     * @param string[] $nodeTypes
     */
    public function __construct(
        string $package,
        string $name,
        string $category,
        array $nodeTypes,
        string $description,
        string $codeBefore,
        string $codeAfter,
        string $source,
        ?string $levelConfig
    ) {
        $this->package = $package;
        $this->setName($name);
        $this->category = $category;
        $this->nodeTypes = $nodeTypes;
        $this->codeBefore = $codeBefore;
        $this->codeAfter = $codeAfter;
        $this->description = $description;
        $this->source = $source;
        $this->levelConfig = $levelConfig;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getPackage(): string
    {
        return $this->package;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return $this->nodeTypes;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function getCodeBefore(): string
    {
        return $this->codeBefore;
    }

    public function getCodeAfter(): string
    {
        return $this->codeAfter;
    }

    public function getSource(): string
    {
        return $this->source;
    }

    public function getLevelConfig(): ?string
    {
        return $this->levelConfig;
    }

    private function setName(string $name): void
    {
        if (! Strings::endsWith($name, 'Rector')) {
            $name .= 'Rector';
        }

        $this->name = $name;
    }
}
