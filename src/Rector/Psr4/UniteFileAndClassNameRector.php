<?php declare(strict_types=1);

namespace Rector\Rector\Psr4;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassLike;
use Rector\FileSystemRector\Contract\FileSystemRectorInterface;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\PhpParser\Node\Resolver\NameResolver;
use Rector\PhpParser\Parser\Parser;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;
use Symfony\Component\Filesystem\Filesystem;
use Symplify\PackageBuilder\FileSystem\SmartFileInfo;

final class UniteFileAndClassNameRector implements FileSystemRectorInterface
{
    /**
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var Parser
     */
    private $parser;

    /**
     * @var NameResolver
     */
    private $nameResolver;

    public function __construct(
        BetterNodeFinder $betterNodeFinder,
        Filesystem $filesystem,
        Parser $parser,
        NameResolver $nameResolver
    ) {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->filesystem = $filesystem;
        $this->parser = $parser;
        $this->nameResolver = $nameResolver;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Corrects file name to be in sync with class name in it and autoloadable by PSR-4 classes.',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
// file: SomeClass.php
final class SomeCllass 
{
    
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
// file: SomeClass.php
final class SomeClass 
{
    
}
CODE_SAMPLE
                ),
            ]
        );
    }

    public function refactor(SmartFileInfo $smartFileInfo): void
    {
        $nodes = $this->parser->parseFile($smartFileInfo->getRealPath());

        // collect named class nodes
        /** @var ClassLike[] $classLikes */
        $classLikes = $this->betterNodeFinder->find($nodes, function (Node $node) {
            if (! $node instanceof ClassLike) {
                return false;
            }

            return $node->name !== null;
        });

        // process files with exactly 1 non-anonymous class like nodes
        if (count($classLikes) !== 1) {
            return;
        }

        $classLikeShortName = $this->nameResolver->resolve($classLikes[0]);
        if ($classLikeShortName === null) {
            return;
        }

        // is in sync with file? skip
        if ($classLikeShortName === $smartFileInfo->getBasenameWithoutSuffix()) {
            return;
        }

        // rename!
        $oldPath = $smartFileInfo->getRealPath();
        $newPath = dirname($smartFileInfo->getRealPath()) . DIRECTORY_SEPARATOR . $classLikeShortName . '.php';

        $this->filesystem->rename($oldPath, $newPath);
    }
}
