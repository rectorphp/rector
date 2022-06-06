<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Symfony;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Stmt\ClassLike;
use RectorPrefix20220606\PhpParser\NodeTraverser;
use RectorPrefix20220606\PhpParser\NodeVisitor\NameResolver;
use RectorPrefix20220606\PHPStan\Reflection\ReflectionProvider;
use RectorPrefix20220606\Rector\Core\PhpParser\Node\BetterNodeFinder;
use RectorPrefix20220606\Rector\Core\PhpParser\Parser\RectorParser;
use RectorPrefix20220606\Rector\NodeNameResolver\NodeNameResolver;
use Symplify\SmartFileSystem\SmartFileInfo;
final class BundleClassResolver
{
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Parser\RectorParser
     */
    private $rectorParser;
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    public function __construct(BetterNodeFinder $betterNodeFinder, NodeNameResolver $nodeNameResolver, RectorParser $rectorParser, ReflectionProvider $reflectionProvider)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->rectorParser = $rectorParser;
        $this->reflectionProvider = $reflectionProvider;
    }
    public function resolveShortBundleClassFromControllerClass(string $class) : ?string
    {
        $classReflection = $this->reflectionProvider->getClass($class);
        // resolve bundle from existing ones
        $fileName = $classReflection->getFileName();
        if ($fileName === null) {
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
        /** @var string[] $bundleFiles */
        if ($bundleFiles === []) {
            return null;
        }
        $bundleFile = $bundleFiles[0];
        $bundleClassName = $this->resolveClassNameFromFilePath($bundleFile);
        if ($bundleClassName !== null) {
            return $this->nodeNameResolver->getShortName($bundleClassName);
        }
        return null;
    }
    private function resolveClassNameFromFilePath(string $filePath) : ?string
    {
        $fileInfo = new SmartFileInfo($filePath);
        $nodes = $this->rectorParser->parseFile($fileInfo);
        $this->addFullyQualifiedNamesToNodes($nodes);
        $classLike = $this->betterNodeFinder->findFirstNonAnonymousClass($nodes);
        if (!$classLike instanceof ClassLike) {
            return null;
        }
        return $this->nodeNameResolver->getName($classLike);
    }
    /**
     * @param Node[] $nodes
     */
    private function addFullyQualifiedNamesToNodes(array $nodes) : void
    {
        $nodeTraverser = new NodeTraverser();
        $nameResolver = new NameResolver();
        $nodeTraverser->addVisitor($nameResolver);
        $nodeTraverser->traverse($nodes);
    }
}
