<?php

declare(strict_types=1);

namespace Rector\Sensio;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use Rector\CodingStyle\Naming\ClassNaming;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\PhpParser\Parser\Parser;
use Rector\NodeNameResolver\NodeNameResolver;
use ReflectionClass;
use Symplify\SmartFileSystem\SmartFileInfo;

final class BundleClassResolver
{
    /**
     * @var Parser
     */
    private $parser;

    /**
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;

    /**
     * @var ClassNaming
     */
    private $classNaming;

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    public function __construct(
        BetterNodeFinder $betterNodeFinder,
        ClassNaming $classNaming,
        NodeNameResolver $nodeNameResolver,
        Parser $parser
    ) {
        $this->parser = $parser;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->classNaming = $classNaming;
        $this->nodeNameResolver = $nodeNameResolver;
    }

    public function resolveShortBundleClassFromControllerClass(string $class): ?string
    {
        // resolve bundle from existing ones
        $reflectionClass = new ReflectionClass($class);

        $fileName = $reflectionClass->getFileName();
        if (! $fileName) {
            return null;
        }

        $controllerDirectory = dirname($fileName);

        $rootFolder = getenv('SystemDrive', true) . DIRECTORY_SEPARATOR;

        // traverse up, un-till first bundle class appears
        $bundleFiles = [];
        while ($bundleFiles === [] && $controllerDirectory !== $rootFolder) {
            $bundleFiles = (array) glob($controllerDirectory . '/**Bundle.php');
            $controllerDirectory = dirname($controllerDirectory);
        }

        if ($bundleFiles === []) {
            return null;
        }

        /** @var string $bundleFile */
        $bundleFile = $bundleFiles[0];

        $bundleClassName = $this->resolveClassNameFromFilePath($bundleFile);
        if ($bundleClassName !== null) {
            return $this->classNaming->getShortName($bundleClassName);
        }

        return null;
    }

    private function resolveClassNameFromFilePath(string $filePath): ?string
    {
        $fileInfo = new SmartFileInfo($filePath);
        $nodes = $this->parser->parseFileInfo($fileInfo);

        $this->addFullyQualifiedNamesToNodes($nodes);

        $class = $this->betterNodeFinder->findFirstNonAnonymousClass($nodes);
        if ($class === null) {
            return null;
        }

        return $this->nodeNameResolver->getName($class);
    }

    /**
     * @param Node[] $nodes
     */
    private function addFullyQualifiedNamesToNodes(array $nodes): void
    {
        $nodeTraverser = new NodeTraverser();
        $nameResolver = new NameResolver();
        $nodeTraverser->addVisitor($nameResolver);

        $nodeTraverser->traverse($nodes);
    }
}
