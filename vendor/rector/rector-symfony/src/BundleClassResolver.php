<?php

declare (strict_types=1);
namespace Rector\Symfony;

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
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @var \Rector\Core\PhpParser\Parser\Parser
     */
    private $parser;
    /**
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    public function __construct(\Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\Core\PhpParser\Parser\Parser $parser, \PHPStan\Reflection\ReflectionProvider $reflectionProvider)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->parser = $parser;
        $this->reflectionProvider = $reflectionProvider;
    }
    public function resolveShortBundleClassFromControllerClass(string $class) : ?string
    {
        $classReflection = $this->reflectionProvider->getClass($class);
        // resolve bundle from existing ones
        $fileName = $classReflection->getFileName();
        if (!$fileName) {
            return null;
        }
        $controllerDirectory = \dirname($fileName);
        $rootFolder = \getenv('SystemDrive', \true) . \DIRECTORY_SEPARATOR;
        // traverse up, un-till first bundle class appears
        $bundleFiles = [];
        while ($bundleFiles === [] && $controllerDirectory !== $rootFolder) {
            $bundleFiles = (array) \glob($controllerDirectory . '/**Bundle.php');
            $controllerDirectory = \dirname($controllerDirectory);
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
    private function resolveClassNameFromFilePath(string $filePath) : ?string
    {
        $fileInfo = new \Symplify\SmartFileSystem\SmartFileInfo($filePath);
        $nodes = $this->parser->parseFileInfo($fileInfo);
        $this->addFullyQualifiedNamesToNodes($nodes);
        $classLike = $this->betterNodeFinder->findFirstNonAnonymousClass($nodes);
        if (!$classLike instanceof \PhpParser\Node\Stmt\ClassLike) {
            return null;
        }
        return $this->nodeNameResolver->getName($classLike);
    }
    /**
     * @param Node[] $nodes
     */
    private function addFullyQualifiedNamesToNodes(array $nodes) : void
    {
        $nodeTraverser = new \PhpParser\NodeTraverser();
        $nameResolver = new \PhpParser\NodeVisitor\NameResolver();
        $nodeTraverser->addVisitor($nameResolver);
        $nodeTraverser->traverse($nodes);
    }
}
