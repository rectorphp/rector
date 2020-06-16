<?php

declare(strict_types=1);

namespace Rector\Refactoring\Rector\FileSystem;

use PhpParser\Node\Stmt\Namespace_;
use Rector\Core\Naming\NamespaceMatcher;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\FileSystemRector\Rector\AbstractFileSystemRector;
use Rector\PSR4\FileRelocationResolver;
use Symplify\SmartFileSystem\SmartFileInfo;

final class MoveAndRenameNamespaceRector extends AbstractFileSystemRector
{
    /**
     * @var string[]
     */
    private $oldToNewNamespace = [];

    /**
     * @var FileRelocationResolver
     */
    private $fileRelocationResolver;

    /**
     * @var NamespaceMatcher
     */
    private $namespaceMatcher;

    /**
     * @param string[] $oldToNewNamespace
     */
    public function __construct(
        FileRelocationResolver $fileRelocationResolver,
        NamespaceMatcher $namespaceMatcher,
        array $oldToNewNamespace = []
    ) {
        $this->fileRelocationResolver = $fileRelocationResolver;
        $this->oldToNewNamespace = $oldToNewNamespace;
        $this->namespaceMatcher = $namespaceMatcher;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Move namespace to new location with respect to PSR-4 + follow up with files in the namespace move', [
                new ConfiguredCodeSample(
                    <<<'CODE_SAMPLE'
// app/Entity/SomeClass.php

namespace App\Entity;

class SomeClass
{
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
// app/ValueObject/SomeClass.php

namespace App\ValueObject;

class SomeClass
{
}
CODE_SAMPLE
                    ,
                    [
                        '$oldToNewNamespace' => [
                            'App\Entity' => 'App\ValueObject',
                        ],
                    ]
                ),
            ]
        );
    }

    public function refactor(SmartFileInfo $smartFileInfo): void
    {
        $namespaceName = $this->resolveNamespaceName($smartFileInfo);
        if ($namespaceName === null) {
            return;
        }

        $renamedNamespaceValueObject = $this->namespaceMatcher->matchRenamedNamespace(
            $namespaceName,
            $this->oldToNewNamespace
        );

        if ($renamedNamespaceValueObject === null) {
            return;
        }

        $newFileLocation = $this->fileRelocationResolver->resolveNewFileLocationFromRenamedNamespace(
            $smartFileInfo,
            $renamedNamespaceValueObject
        );

        $this->moveFile($smartFileInfo, $newFileLocation);
    }

    private function resolveNamespaceName(SmartFileInfo $smartFileInfo): ?string
    {
        $nodes = $this->parseFileInfoToNodesWithoutScope($smartFileInfo);

        /** @var Namespace_|null $namespace */
        $namespace = $this->betterNodeFinder->findFirstInstanceOf($nodes, Namespace_::class);
        if ($namespace === null) {
            return null;
        }

        return $this->getName($namespace);
    }
}
