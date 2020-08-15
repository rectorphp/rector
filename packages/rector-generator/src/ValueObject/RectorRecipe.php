<?php

declare(strict_types=1);

namespace Rector\RectorGenerator\ValueObject;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Util\StaticRectorStrings;
use Rector\RectorGenerator\Exception\ConfigurationException;

final class RectorRecipe
{
    /**
     * @var string
     */
    private const PACKAGE_UTILS = 'Utils';

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $codeBefore;

    /**
     * @var string
     */
    private $codeAfter;

    /**
     * @var bool
     */
    private $isRectorRepository = false;

    /**
     * @var bool
     */
    private $isPhpSnippet = true;

    /**
     * @var string
     */
    private $category;

    /**
     * @var class-string[]
     */
    private $nodeTypes = [];

    /**
     * @var mixed[]
     */
    private $resources = [];

    /**
     * @var mixed[]
     */
    private $configration = [];

    /**
     * @var string|null
     */
    private $package;

    /**
     * @var string|null
     */
    private $set;

    /**
     * @var string|null
     */
    private $extraFileName;

    /**
     * @var string|null
     */
    private $extraFileContent;

    /**
     * @param class-string[] $nodeTypes
     * @param mixed[] $configration
     */
    public function __construct(
        ?string $package,
        string $name,
        array $nodeTypes,
        string $description,
        string $codeBefore,
        string $codeAfter,
        array $resources,
        ?string $set = null,
        array $configration = [],
        ?string $extraFileName = null,
        ?string $extraFileContent = null,
        ?bool $isRectorRepository = null
    ) {
        $this->setName($name);
        $this->setNodeTypes($nodeTypes);

        $this->description = $description;

        $this->setCodeBefore($codeBefore);
        $this->setCodeAfter($codeAfter);
        $this->setResources($resources);

        $this->set = $set;
        $this->configration = $configration;
        $this->extraFileName = $extraFileName;
        $this->setExtraFileContent($extraFileContent);
        $this->setIsRectorRepository($isRectorRepository);

        // last on purpose, depends on isRectorRepository
        $this->setPackage($package);

        $this->resolveCategory();
    }

    public function getPackage(): string
    {
        $recipePackage = $this->package;
        if (! $this->isRectorRepository || $recipePackage === null || $recipePackage === '') {
            return self::PACKAGE_UTILS;
        }

        return $recipePackage;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getNodeTypes(): array
    {
        return $this->nodeTypes;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getCodeBefore(): string
    {
        return $this->codeBefore;
    }

    public function getCodeAfter(): string
    {
        return $this->codeAfter;
    }

    public function getResources(): array
    {
        return $this->resources;
    }

    public function getSet(): ?string
    {
        return $this->set;
    }

    /**
     * @return array<string, mixed>
     */
    public function getConfigration(): array
    {
        return $this->configration;
    }

    public function getExtraFileName(): ?string
    {
        return $this->extraFileName;
    }

    public function getExtraFileContent(): ?string
    {
        return $this->extraFileContent;
    }

    public function isPhpSnippet(): bool
    {
        return $this->isPhpSnippet;
    }

    public function isRectorRepository(): bool
    {
        return $this->isRectorRepository;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function getPackageDirectory(): string
    {
        if (! $this->isRectorRepository) {
            return 'rector';
        }

        // special cases
        if ($this->getPackage() === 'PHPUnit') {
            return 'phpunit';
        }

        return StaticRectorStrings::camelCaseToDashes($this->getPackage());
    }

    private function setName(string $name): void
    {
        if (! Strings::endsWith($name, 'Rector')) {
            $message = sprintf('Rector name "%s" must end with "Rector"', $name);
            throw new ConfigurationException($message);
        }

        $this->name = $name;
    }

    /**
     * @param class-string[] $nodeTypes
     */
    private function setNodeTypes(array $nodeTypes): void
    {
        foreach ($nodeTypes as $nodeType) {
            if (is_a($nodeType, Node::class, true)) {
                continue;
            }

            $message = sprintf(
                'Node type "%s" does not exist, implement "%s" interface or is not imported in "rector-recipe.php"',
                $nodeType,
                Node::class
            );
            throw new ShouldNotHappenException($message);
        }

        if (count($nodeTypes) < 1) {
            $message = sprintf('"$nodeTypes" argument requires at least one item, e.g. "%s"', FuncCall::class);
            throw new ConfigurationException($message);
        }

        $this->nodeTypes = $nodeTypes;
    }

    private function setCodeBefore(string $codeBefore): void
    {
        $this->setIsPhpSnippet($codeBefore);

        $this->codeBefore = $this->normalizeCode($codeBefore);
    }

    private function setCodeAfter(string $codeAfter): void
    {
        $this->codeAfter = $this->normalizeCode($codeAfter);
    }

    private function setResources(array $resources): void
    {
        $this->resources = array_filter($resources);
    }

    private function setExtraFileContent(?string $extraFileContent): void
    {
        if ($extraFileContent === null) {
            return;
        }

        $this->extraFileContent = $this->normalizeCode($extraFileContent);
    }

    private function setIsRectorRepository(?bool $isRectorRepository): void
    {
        $this->isRectorRepository = $isRectorRepository ?? file_exists(__DIR__ . '/../../../../vendor');
    }

    private function setPackage(?string $package): void
    {
        if ($package !== '' && $package !== null && $package !== self::PACKAGE_UTILS) {
            $this->package = $package;
            return;
        }

        // only can be empty or utils when outside Rector repository
        if (! $this->isRectorRepository) {
            $this->package = $package;
            return;
        }

        $message = sprintf('Parameter "package" cannot be empty or "Utils", when in Rector repository');
        throw new ConfigurationException($message);
    }

    private function resolveCategory(): void
    {
        $this->category = (string) Strings::after($this->nodeTypes[0], '\\', -1);
    }

    private function setIsPhpSnippet(string $codeBefore): void
    {
        $this->isPhpSnippet = Strings::startsWith($codeBefore, '<?php');
    }

    private function normalizeCode(string $code): string
    {
        if (Strings::startsWith($code, '<?php')) {
            $code = ltrim($code, '<?php');
        }

        return trim($code);
    }
}
