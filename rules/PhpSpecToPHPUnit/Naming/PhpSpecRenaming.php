<?php

declare (strict_types=1);
namespace Rector\PhpSpecToPHPUnit\Naming;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Namespace_;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\Util\StaticRectorStrings;
use Rector\NodeNameResolver\NodeNameResolver;
use RectorPrefix20220209\Symplify\PackageBuilder\Strings\StringFormatConverter;
final class PhpSpecRenaming
{
    /**
     * @var string
     */
    private const SPEC = 'Spec';
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Symplify\PackageBuilder\Strings\StringFormatConverter
     */
    private $stringFormatConverter;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    public function __construct(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \RectorPrefix20220209\Symplify\PackageBuilder\Strings\StringFormatConverter $stringFormatConverter, \Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->stringFormatConverter = $stringFormatConverter;
        $this->betterNodeFinder = $betterNodeFinder;
    }
    public function renameMethod(\PhpParser\Node\Stmt\ClassMethod $classMethod) : void
    {
        if ($classMethod->isPrivate()) {
            return;
        }
        $classMethodName = $this->nodeNameResolver->getName($classMethod);
        $classMethodName = $this->removeNamePrefixes($classMethodName);
        // from PhpSpec to PHPUnit method naming convention
        $classMethodName = $this->stringFormatConverter->underscoreAndHyphenToCamelCase($classMethodName);
        // add "test", so PHPUnit runs the method
        if (\strncmp($classMethodName, 'test', \strlen('test')) !== 0) {
            $classMethodName = 'test' . \ucfirst($classMethodName);
        }
        $classMethod->name = new \PhpParser\Node\Identifier($classMethodName);
    }
    public function renameExtends(\PhpParser\Node\Stmt\Class_ $class) : void
    {
        $class->extends = new \PhpParser\Node\Name\FullyQualified('PHPUnit\\Framework\\TestCase');
    }
    public function renameNamespace(\PhpParser\Node\Stmt\Class_ $class) : void
    {
        $namespace = $this->betterNodeFinder->findParentType($class, \PhpParser\Node\Stmt\Namespace_::class);
        if (!$namespace instanceof \PhpParser\Node\Stmt\Namespace_) {
            return;
        }
        $namespaceName = $this->nodeNameResolver->getName($namespace);
        if ($namespaceName === null) {
            return;
        }
        $newNamespaceName = \Rector\Core\Util\StaticRectorStrings::removePrefixes($namespaceName, ['spec\\']);
        $namespace->name = new \PhpParser\Node\Name('Tests\\' . $newNamespaceName);
    }
    public function renameClass(\PhpParser\Node\Stmt\Class_ $class) : void
    {
        $classShortName = $this->nodeNameResolver->getShortName($class);
        // anonymous class?
        if ($classShortName === '') {
            throw new \Rector\Core\Exception\ShouldNotHappenException();
        }
        // 2. change class name
        $newClassName = \Rector\Core\Util\StaticRectorStrings::removeSuffixes($classShortName, [self::SPEC]);
        $newTestClassName = $newClassName . 'Test';
        $class->name = new \PhpParser\Node\Identifier($newTestClassName);
    }
    public function resolveObjectPropertyName(\PhpParser\Node\Stmt\Class_ $class) : string
    {
        // anonymous class?
        if ($class->name === null) {
            throw new \Rector\Core\Exception\ShouldNotHappenException();
        }
        $shortClassName = $this->nodeNameResolver->getShortName($class);
        $bareClassName = \Rector\Core\Util\StaticRectorStrings::removeSuffixes($shortClassName, [self::SPEC, 'Test']);
        return \lcfirst($bareClassName);
    }
    public function resolveTestedClass(\PhpParser\Node $node) : string
    {
        if ($node instanceof \PhpParser\Node\Stmt\ClassLike) {
            $className = (string) $this->nodeNameResolver->getName($node);
        } else {
            $classLike = $this->betterNodeFinder->findParentType($node, \PhpParser\Node\Stmt\ClassLike::class);
            if (!$classLike instanceof \PhpParser\Node\Stmt\ClassLike) {
                throw new \Rector\Core\Exception\ShouldNotHappenException();
            }
            $className = (string) $this->nodeNameResolver->getName($classLike);
        }
        $newClassName = \Rector\Core\Util\StaticRectorStrings::removePrefixes($className, ['spec\\']);
        return \Rector\Core\Util\StaticRectorStrings::removeSuffixes($newClassName, [self::SPEC]);
    }
    private function removeNamePrefixes(string $name) : string
    {
        $originalName = $name;
        $name = \Rector\Core\Util\StaticRectorStrings::removePrefixes($name, ['it_should_have_', 'it_should_be', 'it_should_', 'it_is_', 'it_', 'is_']);
        if ($name === '') {
            return $originalName;
        }
        return $name;
    }
}
