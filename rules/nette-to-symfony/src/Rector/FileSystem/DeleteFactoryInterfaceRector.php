<?php

declare(strict_types=1);

namespace Rector\NetteToSymfony\Rector\FileSystem;

use PhpParser\Node\Stmt\Interface_;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\FileSystemRector\Rector\AbstractFileSystemRector;
use Rector\NetteToSymfony\Analyzer\NetteControlFactoryInterfaceAnalyzer;
use Symplify\SmartFileSystem\SmartFileInfo;

/**
 * @see \Rector\NetteToSymfony\Tests\Rector\FIleSystem\DeleteFactoryInterfaceRector\DeleteFactoryInterfaceFileSystemRectorTest
 */
final class DeleteFactoryInterfaceRector extends AbstractFileSystemRector
{
    /**
     * @var NetteControlFactoryInterfaceAnalyzer
     */
    private $netteControlFactoryInterfaceAnalyzer;

    public function __construct(NetteControlFactoryInterfaceAnalyzer $netteControlFactoryInterfaceAnalyzer)
    {
        $this->netteControlFactoryInterfaceAnalyzer = $netteControlFactoryInterfaceAnalyzer;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Interface factories are not needed in Symfony. Clear constructor injection is used instead', [
                new CodeSample(
                    <<<'CODE_SAMPLE'
interface SomeControlFactoryInterface
{
    public function create();
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
CODE_SAMPLE
                ),
            ]
        );
    }

    public function refactor(SmartFileInfo $smartFileInfo): void
    {
        $nodes = $this->parseFileInfoToNodes($smartFileInfo);

        /** @var Interface_|null $interface */
        $interface = $this->betterNodeFinder->findFirstInstanceOf($nodes, Interface_::class);
        if ($interface === null) {
            return;
        }

        if (! $this->netteControlFactoryInterfaceAnalyzer->isComponentFactoryInterface($interface)) {
            return;
        }

        $this->removeFile($smartFileInfo);
    }
}
