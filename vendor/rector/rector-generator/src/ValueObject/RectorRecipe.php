<?php

declare (strict_types=1);
namespace Rector\RectorGenerator\ValueObject;

use RectorPrefix20220531\Nette\Utils\Json;
use RectorPrefix20220531\Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use Rector\RectorGenerator\Exception\ConfigurationException;
use Rector\RectorGenerator\Exception\ShouldNotHappenException;
final class RectorRecipe
{
    /**
     * @var string
     */
    private const PACKAGE_UTILS = 'Utils';
    /**
     * @var string|null
     */
    private $name;
    /**
     * @var string|null
     */
    private $codeBefore;
    /**
     * @var string|null
     */
    private $codeAfter;
    /**
     * @var bool
     */
    private $isRectorRepository = \false;
    /**
     * @var string|null
     */
    private $category;
    /**
     * @var class-string[]
     */
    private $nodeTypes = [];
    /**
     * @var string[]
     */
    private $resources = [];
    /**
     * The key is a private property name that holds the configuration value (can be multiple properties)
     *
     * @var array<string, mixed[]>
     */
    private $configuration = [];
    /**
     * Use default package name, if not overriden manually
     * @var string
     */
    private $package = self::PACKAGE_UTILS;
    /**
     * @var string|null
     */
    private $setFilePath;
    /**
     * @var string
     */
    private $description;
    /**
     * @param array<class-string<Node>> $nodeTypes
     */
    public function __construct(string $package, string $name, array $nodeTypes, string $description, string $codeBefore, string $codeAfter)
    {
        $this->description = $description;
        $this->isRectorRepository = $this->detectRectorRepository();
        $this->setPackage($package);
        $this->setName($name);
        $this->setNodeTypes($nodeTypes);
        if ($codeBefore === $codeAfter) {
            throw new \Rector\RectorGenerator\Exception\ConfigurationException('Code before and after are identical. They have to be different');
        }
        $this->setCodeBefore($codeBefore);
        $this->setCodeAfter($codeAfter);
        $this->resolveCategory($nodeTypes);
    }
    public function getPackage() : string
    {
        return $this->package;
    }
    public function getName() : string
    {
        return $this->name;
    }
    /**
     * @return class-string[]
     */
    public function getNodeTypes() : array
    {
        return $this->nodeTypes;
    }
    public function getDescription() : string
    {
        return $this->description;
    }
    public function getCodeBefore() : string
    {
        return $this->codeBefore;
    }
    public function getCodeAfter() : string
    {
        return $this->codeAfter;
    }
    /**
     * @return string[]
     */
    public function getResources() : array
    {
        return $this->resources;
    }
    public function setSetFilePath(string $setFilePath) : void
    {
        $this->setFilePath = $setFilePath;
    }
    public function getSetFilePath() : ?string
    {
        return $this->setFilePath;
    }
    /**
     * @return array<string, mixed[]>
     */
    public function getConfiguration() : array
    {
        return $this->configuration;
    }
    public function isRectorRepository() : bool
    {
        return $this->isRectorRepository;
    }
    public function getCategory() : string
    {
        return $this->category;
    }
    /**
     * @api
     * @param array<string, mixed[]> $configuration
     */
    public function setConfiguration(array $configuration) : void
    {
        $this->configuration = $configuration;
    }
    /**
     * @api
     * @param string[] $resources
     */
    public function setResources(array $resources) : void
    {
        $this->resources = \array_filter($resources);
    }
    /**
     * For testing purposes
     *
     * @api
     */
    public function setIsRectorRepository(bool $isRectorRepository) : void
    {
        $this->isRectorRepository = $isRectorRepository;
    }
    /**
     * For tests
     *
     * @api
     */
    public function setPackage(string $package) : void
    {
        if (\is_file($package)) {
            $message = \sprintf('The "%s()" method only accepts package name, file path "%s" given', __METHOD__, $package);
            throw new \Rector\RectorGenerator\Exception\ShouldNotHappenException($message);
        }
        $this->package = $package;
    }
    private function setName(string $name) : void
    {
        if (\substr_compare($name, 'Rector', -\strlen('Rector')) !== 0) {
            $message = \sprintf('Rector name "%s" must end with "Rector"', $name);
            throw new \Rector\RectorGenerator\Exception\ConfigurationException($message);
        }
        $this->name = $name;
    }
    /**
     * @param class-string[] $nodeTypes
     */
    private function setNodeTypes(array $nodeTypes) : void
    {
        foreach ($nodeTypes as $nodeType) {
            if (\is_a($nodeType, \PhpParser\Node::class, \true)) {
                continue;
            }
            $message = \sprintf('Node type "%s" does not exist, implement "%s" interface or is not imported in "rector-recipe.php"', $nodeType, \PhpParser\Node::class);
            throw new \Rector\RectorGenerator\Exception\ShouldNotHappenException($message);
        }
        if (\count($nodeTypes) < 1) {
            $message = \sprintf('"$nodeTypes" argument requires at least one item, e.g. "%s"', \PhpParser\Node\Expr\FuncCall::class);
            throw new \Rector\RectorGenerator\Exception\ConfigurationException($message);
        }
        $this->nodeTypes = $nodeTypes;
    }
    private function setCodeBefore(string $codeBefore) : void
    {
        $this->codeBefore = $this->normalizeCode($codeBefore);
    }
    private function setCodeAfter(string $codeAfter) : void
    {
        $this->codeAfter = $this->normalizeCode($codeAfter);
    }
    /**
     * @param string[] $nodeTypes
     */
    private function resolveCategory(array $nodeTypes) : void
    {
        $this->category = (string) \RectorPrefix20220531\Nette\Utils\Strings::after($nodeTypes[0], '\\', -1);
    }
    private function normalizeCode(string $code) : string
    {
        if (\strncmp($code, '<?php', \strlen('<?php')) === 0) {
            $code = \ltrim($code, '<?php');
        }
        return \trim($code);
    }
    private function detectRectorRepository() : bool
    {
        $possibleComposerJsonFilePaths = [__DIR__ . '/../../../../../composer.json', __DIR__ . '/../../composer.json'];
        foreach ($possibleComposerJsonFilePaths as $possibleComposerJsonFilePath) {
            if (!\file_exists($possibleComposerJsonFilePath)) {
                continue;
            }
            $composerJsonContent = \file_get_contents($possibleComposerJsonFilePath);
            if ($composerJsonContent === \false) {
                continue;
            }
            $composerJson = \RectorPrefix20220531\Nette\Utils\Json::decode($composerJsonContent, \RectorPrefix20220531\Nette\Utils\Json::FORCE_ARRAY);
            if (!isset($composerJson['name'])) {
                continue;
            }
            if (\strncmp($composerJson['name'], 'rector/', \strlen('rector/')) === 0) {
                return \true;
            }
        }
        return \false;
    }
}
