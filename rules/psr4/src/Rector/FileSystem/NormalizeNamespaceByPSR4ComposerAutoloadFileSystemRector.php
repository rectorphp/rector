<?php

declare(strict_types=1);

namespace Rector\PSR4\Rector\FileSystem;

use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Namespace_;
use Rector\Autodiscovery\ValueObject\NodesWithFileDestination;
use Rector\Core\RectorDefinition\ComposerJsonAwareCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\FileSystemRector\Rector\AbstractFileSystemRector;
use Rector\PSR4\Contract\PSR4AutoloadNamespaceMatcherInterface;
use Rector\PSR4\Rector\MultipleClassFileToPsr4ClassesRector;
use Symplify\SmartFileSystem\SmartFileInfo;

/**
 * @sponsor Thanks https://amateri.com for sponsoring this rule - visit them on https://www.startupjobs.cz/startup/scrumworks-s-r-o
 *
 * @see \Rector\PSR4\Tests\Rector\FileSystem\NormalizeNamespaceByPSR4ComposerAutoloadFileSystemRector\NormalizeNamespaceByPSR4ComposerAutoloadFileSystemRectorTest
 */
final class NormalizeNamespaceByPSR4ComposerAutoloadFileSystemRector extends AbstractFileSystemRector
{
    /**
     * @var PSR4AutoloadNamespaceMatcherInterface
     */
    private $psr4AutoloadNamespaceMatcher;

    public function __construct(PSR4AutoloadNamespaceMatcherInterface $psr4AutoloadNamespaceMatcher)
    {
        $this->psr4AutoloadNamespaceMatcher = $psr4AutoloadNamespaceMatcher;
    }

    public function getDefinition(): RectorDefinition
    {
        $description = sprintf(
            'Adds namespace to namespace-less files to match PSR-4 in `composer.json` autoload section. Run with combination with %s',
            MultipleClassFileToPsr4ClassesRector::class
        );

        return new RectorDefinition($description, [
            new ComposerJsonAwareCodeSample(
                <<<'CODE_SAMPLE'
// src/SomeClass.php

class SomeClass
{
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
// src/SomeClass.php

namespace App\CustomNamespace;

class SomeClass
{
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
{
    "autoload": {
        "psr-4": {
            "App\\CustomNamespace\\": "src"
        }
    }
}
CODE_SAMPLE
            ),
        ]);
    }

    public function refactor(SmartFileInfo $smartFileInfo): void
    {
        $nodes = $this->parseFileInfoToNodes($smartFileInfo);

        /** @var Namespace_|null $namespace */
        $namespace = $this->betterNodeFinder->findFirstInstanceOf($nodes, Namespace_::class);
        if ($namespace !== null) {
            return;
        }

        $expectedNamespace = $this->psr4AutoloadNamespaceMatcher->getExpectedNamespace($nodes[0]);
        if ($expectedNamespace === null) {
            return;
        }

        $namespace = new Namespace_(new Name($expectedNamespace));
        $namespace->stmts = $nodes;

        $nodesWithFileDestination = new NodesWithFileDestination([
            $namespace,
        ], $smartFileInfo->getRealPath(), $smartFileInfo);
        $this->printNodesWithFileDestination($nodesWithFileDestination);
    }
}
