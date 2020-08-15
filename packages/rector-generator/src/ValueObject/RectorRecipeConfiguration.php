<?php

declare(strict_types=1);

namespace Rector\RectorGenerator\ValueObject;

use Nette\Utils\Strings;
use PhpParser\Node;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Util\StaticRectorStrings;
use Symplify\SetConfigResolver\ValueObject\Set;
use Symplify\SmartFileSystem\SmartFileInfo;

final class RectorRecipeConfiguration
{
    /**
     * @var string
     */
    public const PACKAGE_UTILS = 'Utils';

    /**
     * @var bool
     */
    private $isRectorRepository = false;

    /**
     * @var string
     */
    private $name;

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
     * @var bool
     */
    private $isPhpSnippet = false;

    /**
     * @var string[]
     */
    private $nodeTypes = [];

    /**
     * @var string[]
     */
    private $source = [];

    /**
     * @var array<string, mixed>
     */
    private $ruleConfiguration = [];

    /**
     * @var string|null
     */
    private $package;

    /**
     * @var Set|null
     */
    private $set;

    /**
     * @var string|null
     */
    private $extraFileContent;

    /**
     * @var string|null
     */
    private $extraFileName;

    /**
     * @param string[] $nodeTypes
     * @param string[] $source
     */
    public function __construct(
        ?string $package,
        string $name,
        string $category,
        array $nodeTypes,
        string $description,
        string $codeBefore,
        string $codeAfter,
        ?string $extraFileContent,
        ?string $extraFileName,
        array $ruleConfiguration,
        array $source,
        ?Set $set,
        bool $isPhpSnippet,
        bool $isRectorRepository
    ) {
        $this->package = $package;
        $this->setName($name);
        $this->category = $category;
        $this->setNodeTypes($nodeTypes);
        $this->codeBefore = $codeBefore;
        $this->codeAfter = $codeAfter;
        $this->description = $description;
        $this->source = $source;
        $this->set = $set;
        $this->isPhpSnippet = $isPhpSnippet;
        $this->extraFileContent = $extraFileContent;
        $this->extraFileName = $extraFileName;
        $this->ruleConfiguration = $ruleConfiguration;
        $this->isRectorRepository = $isRectorRepository;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getPackage(): string
    {
        if (! $this->isRectorRepository || $this->package === null || $this->package === '') {
            return self::PACKAGE_UTILS;
        }

        return $this->package;
    }

    public function getPackageDirectory(): string
    {
        if (! $this->isRectorRepository) {
            return 'rector';
        }

        // special cases
        if ($this->package === 'PHPUnit') {
            return 'phpunit';
        }

        return StaticRectorStrings::camelCaseToDashes($this->getPackage());
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

    /**
     * @return string[]
     */
    public function getSource(): array
    {
        return $this->source;
    }

    public function getSetConfig(): ?SmartFileInfo
    {
        if ($this->set === null) {
            return null;
        }

        return $this->set->getSetFileInfo();
    }

    public function isPhpSnippet(): bool
    {
        return $this->isPhpSnippet;
    }

    public function getExtraFileContent(): ?string
    {
        return $this->extraFileContent;
    }

    public function getExtraFileName(): ?string
    {
        return $this->extraFileName;
    }

    /**
     * @return array<string, mixed>
     */
    public function getRuleConfiguration(): array
    {
        return $this->ruleConfiguration;
    }

    public function isRectorRepository(): bool
    {
        return $this->isRectorRepository;
    }

    private function setName(string $name): void
    {
        if (! Strings::endsWith($name, 'Rector')) {
            $message = sprintf('Rector name "%s" must end with "Rector"', $name);
            throw new ShouldNotHappenException($message);
        }

        $this->name = $name;
    }

    /**
     * @param string[] $nodeTypes
     */
    private function setNodeTypes(array $nodeTypes): void
    {
        foreach ($nodeTypes as $nodeType) {
            if (! is_a($nodeType, Node::class, true)) {
                $message = sprintf(
                    'Node type "%s" does not exist, implement "%s" interface, or not imported in Rector recipe',
                    $nodeType,
                    Node::class
                );
                throw new ShouldNotHappenException($message);
            }
        }

        $this->nodeTypes = $nodeTypes;
    }
}
