<?php

declare(strict_types=1);

namespace Rector\Autodiscovery\Rector\FileSystem;

use Nette\Utils\Strings;
use PhpParser\Node\Stmt\Class_;
use Rector\Autodiscovery\Analyzer\ClassAnalyzer;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\FileSystemRector\Rector\AbstractFileMovingFileSystemRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\SmartFileSystem\SmartFileInfo;

/**
 * Inspiration @see https://github.com/rectorphp/rector/pull/1865/files#diff-0d18e660cdb626958662641b491623f8
 * @wip
 *
 * @sponsor Thanks https://spaceflow.io/ for sponsoring this rule - visit them on https://github.com/SpaceFlow-app
 *
 * @see \Rector\Autodiscovery\Tests\Rector\FileSystem\MoveValueObjectsToValueObjectDirectoryRector\MoveValueObjectsToValueObjectDirectoryRectorTest
 */
final class MoveValueObjectsToValueObjectDirectoryRector extends AbstractFileMovingFileSystemRector
{
    /**
     * @var string[]
     */
    private const COMMON_SERVICE_SUFFIXES = [
        'Repository', 'Command', 'Mapper', 'Controller', 'Presenter', 'Factory', 'Test', 'TestCase', 'Service',
    ];

    /**
     * @var ClassAnalyzer
     */
    private $classAnalyzer;

    /**
     * @var string[]
     */
    private $types = [];

    /**
     * @var string[]
     */
    private $suffixes = [];

    /**
     * @var bool
     */
    private $enableValueObjectGuessing = true;

    /**
     * @param string[] $types
     * @param string[] $suffixes
     */
    public function __construct(
        ClassAnalyzer $classAnalyzer,
        array $types = [],
        array $suffixes = [],
        bool $enableValueObjectGuessing = true
    ) {
        $this->classAnalyzer = $classAnalyzer;
        $this->types = $types;
        $this->suffixes = $suffixes;
        $this->enableValueObjectGuessing = $enableValueObjectGuessing;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Move value object to ValueObject namespace/directory', [
            new CodeSample(
                <<<'CODE_SAMPLE'
// app/Exception/Name.php
class Name
{
    private $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }
}
CODE_SAMPLE
,
                <<<'CODE_SAMPLE'
// app/ValueObject/Name.php
class Name
{
    private $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }
}
CODE_SAMPLE
            ),
        ]);
    }

    public function refactor(SmartFileInfo $smartFileInfo): void
    {
        $nodes = $this->parseFileInfoToNodes($smartFileInfo);

        /** @var Class_|null $class */
        $class = $this->betterNodeFinder->findFirstInstanceOf($nodes, Class_::class);
        if ($class === null) {
            return;
        }

        if (! $this->isValueObjectMatch($class)) {
            return;
        }

        $nodesWithFileDestination = $this->fileMover->createMovedNodesAndFilePath(
            $smartFileInfo,
            $nodes,
            'ValueObject'
        );

        $this->processNodesWithFileDestination($nodesWithFileDestination);
    }

    private function isValueObjectMatch(Class_ $class): bool
    {
        if ($this->isSuffixMatch($class)) {
            return true;
        }

        $className = $this->getName($class);
        if ($className === null) {
            return false;
        }

        foreach ($this->types as $type) {
            if (is_a($className, $type, true)) {
                return true;
            }
        }

        if ($this->isKnownServiceType($className)) {
            return false;
        }

        if (! $this->enableValueObjectGuessing) {
            return false;
        }

        return $this->classAnalyzer->isValueObjectClass($class);
    }

    private function isKnownServiceType(string $className): bool
    {
        foreach (self::COMMON_SERVICE_SUFFIXES as $commonServiceSuffix) {
            if (Strings::endsWith($className, $commonServiceSuffix)) {
                return true;
            }
        }

        return false;
    }

    private function isSuffixMatch(Class_ $class): bool
    {
        $className = $class->getAttribute(AttributeKey::CLASS_NAME);
        if ($className !== null) {
            foreach ($this->suffixes as $suffix) {
                if (Strings::endsWith($className, $suffix)) {
                    return true;
                }
            }
        }

        return false;
    }
}
