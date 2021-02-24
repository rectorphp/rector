<?php

declare(strict_types=1);

namespace Rector\Sensio;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PHPStan\Reflection\ReflectionProvider;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\PhpParser\Parser\Parser;
use Rector\NodeNameResolver\NodeNameResolver;
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
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var ReflectionProvider
     */
    private $reflectionProvider;

    public function __construct(
        BetterNodeFinder $betterNodeFinder,
        NodeNameResolver $nodeNameResolver,
        Parser $parser,
        ReflectionProvider $reflectionProvider
    ) {
        $this->parser = $parser;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->reflectionProvider = $reflectionProvider;
    }

    public function resolveShortBundleClassFromControllerClass(string $class): ?string
    {
        $classReflection = $this->reflectionProvider->getClass($class);

        // resolve bundle from existing ones
        $fileName = $classReflection->getFileName();
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
            return $this->nodeNameResolver->getShortName($bundleClassName);
        }

        return null;
    }

    private function resolveClassNameFromFilePath(string $filePath): ?string
    {
        $fileInfo = new SmartFileInfo($filePath);
        $nodes = $this->parser->parseFileInfo($fileInfo);

        $this->addFullyQualifiedNamesToNodes($nodes);

        $classLike = $this->betterNodeFinder->findFirstNonAnonymousClass($nodes);
        if (! $classLike instanceof ClassLike) {
            return null;
        }

        return $this->nodeNameResolver->getName($classLike);
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
