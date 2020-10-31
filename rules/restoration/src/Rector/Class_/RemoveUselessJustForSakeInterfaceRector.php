<?php

declare(strict_types=1);

namespace Rector\Restoration\Rector\Class_;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Interface_;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\PSR4\Collector\RenamedClassesCollector;
use Rector\Testing\PHPUnit\StaticPHPUnitEnvironment;
use ReflectionClass;
use Symplify\SmartFileSystem\SmartFileInfo;

/**
 * @sponsor Thanks https://amateri.com for sponsoring this rule - visit them on https://www.startupjobs.cz/startup/scrumworks-s-r-o
 *
 * @see \Rector\Restoration\Tests\Rector\Class_\RemoveUselessJustForSakeInterfaceRector\RemoveUselessJustForSakeInterfaceRectorTest
 */
final class RemoveUselessJustForSakeInterfaceRector extends AbstractRector
{
    /**
     * @var string
     */
    private $interfacePattern;

    /**
     * @var RenamedClassesCollector
     */
    private $renamedClassesCollector;

    public function __construct(
        RenamedClassesCollector $renamedClassesCollector,
        string $interfacePattern = '#(.*?)#'
    ) {
        $this->interfacePattern = $interfacePattern;
        $this->renamedClassesCollector = $renamedClassesCollector;
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }

    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if ((array) $node->implements === []) {
            return null;
        }

        foreach ($node->implements as $key => $implement) {
            $implementedInterfaceName = $this->getName($implement);
            if ($implementedInterfaceName === null) {
                return null;
            }

            if (! Strings::match($implementedInterfaceName, $this->interfacePattern)) {
                continue;
            }

            // is interface in /vendor? probably useful
            $classFileLocation = $this->resolveClassFileLocation($implementedInterfaceName);
            if (Strings::contains($classFileLocation, 'vendor')) {
                continue;
            }

            $interfaceImplementers = $this->getInterfaceImplementers($implementedInterfaceName);

            // makes sense
            if (count($interfaceImplementers) > 1) {
                continue;
            }

            // 1. replace current interface with one more parent or remove it
            $this->removeOrReplaceImlementedInterface($implementedInterfaceName, $node, $key);

            // 2. remove file if not in /vendor
            $this->removeInterfaceFile($implementedInterfaceName, $classFileLocation);

            // 3. replace interface with explicit current class
            $this->replaceName($node, $implementedInterfaceName);
        }

        return null;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Remove interface, that are added just for its sake, but nowhere useful', [
            new CodeSample(
<<<'CODE_SAMPLE'
class SomeClass implements OnlyHereUsedInterface
{
}

interface OnlyHereUsedInterface
{
}

class SomePresenter
{
    public function __construct(OnlyHereUsedInterface $onlyHereUsed)
    {
    }
}
CODE_SAMPLE
,
<<<'CODE_SAMPLE'
class SomeClass
{
}

class SomePresenter
{
    public function __construct(SomeClass $onlyHereUsed)
    {
    }
}
CODE_SAMPLE
            ),
        ]);
    }

    private function resolveClassFileLocation(string $implementedInterfaceName): string
    {
        $reflectionClass = new ReflectionClass($implementedInterfaceName);
        $fileName = $reflectionClass->getFileName();
        if (! $fileName) {
            throw new ShouldNotHappenException();
        }

        return $fileName;
    }

    /**
     * @return class-string[]
     */
    private function getInterfaceImplementers(string $interfaceName): array
    {
        return array_filter(
            get_declared_classes(),
            function (string $className) use ($interfaceName): bool {
                return in_array($interfaceName, class_implements($className), true);
            }
        );
    }

    private function removeOrReplaceImlementedInterface(string $implementedInterfaceName, Class_ $class, int $key): void
    {
        $parentInterface = $this->getParentInterfaceIfFound($implementedInterfaceName);
        if ($parentInterface !== null) {
            $class->implements[$key] = new FullyQualified($parentInterface);
        } else {
            unset($class->implements[$key]);
        }
    }

    private function removeInterfaceFile(string $interfaceName, string $classFileLocation): void
    {
        if (StaticPHPUnitEnvironment::isPHPUnitRun()) {
            $interface = $this->nodeRepository->findInterface($interfaceName);
            if ($interface instanceof Interface_) {
                $this->removeNode($interface);
            }
        } else {
            $smartFileInfo = new SmartFileInfo($classFileLocation);
            $this->removeFile($smartFileInfo);
        }
    }

    private function replaceName(Class_ $class, string $implementedInterfaceName): void
    {
        $className = $this->getName($class);
        if ($className === null) {
            return;
        }

        $this->renamedClassesCollector->addClassRename($implementedInterfaceName, $className);
    }

    private function getParentInterfaceIfFound(string $implementedInterfaceName): ?string
    {
        $reflectionClass = new ReflectionClass($implementedInterfaceName);

        // get first parent interface
        return $reflectionClass->getInterfaceNames()[0] ?? null;
    }
}
