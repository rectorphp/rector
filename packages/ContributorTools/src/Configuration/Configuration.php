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
     * @param string[] $nodeTypes
     */
    public function __construct(
        string $package,
        string $name,
        string $category,
        array $nodeTypes,
        string $description,
        string $codeBefore,
        string $codeAfter
    ) {
        $this->package = $package;
        $this->setName($name);
        $this->category = $category;
        $this->nodeTypes = $nodeTypes;
        $this->codeBefore = $codeBefore;
        $this->codeAfter = $codeAfter;
        $this->description = $description;
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

    private function setName(string $name): void
    {
        if (! Strings::endsWith($name, 'Rector')) {
            $name .= 'Rector';
        }

        $this->name = $name;
    }
}
