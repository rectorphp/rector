<?php

declare (strict_types=1);
namespace Rector\CodingStyle\ClassNameImport;

use RectorPrefix20211123\Nette\Utils\Reflection;
use RectorPrefix20211123\Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Namespace_;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\Reflection\ReflectionProvider;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\CodingStyle\NodeAnalyzer\UseImportNameMatcher;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\ValueObject\Application\File;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use ReflectionClass;
use RectorPrefix20211123\Symfony\Contracts\Service\Attribute\Required;
use RectorPrefix20211123\Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;
use RectorPrefix20211123\Symplify\SimplePhpDocParser\PhpDocNodeTraverser;
/**
 * @see \Rector\Tests\CodingStyle\ClassNameImport\ShortNameResolver\ShortNameResolverTest
 */
final class ShortNameResolver
{
    /**
     * @var string
     * @see https://regex101.com/r/KphLd2/1
     */
    private const BIG_LETTER_START_REGEX = '#^[A-Z]#';
    /**
     * @var string[][]
     */
    private $shortNamesByFilePath = [];
    /**
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    /**
     * @var \Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    /**
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @var \Rector\CodingStyle\NodeAnalyzer\UseImportNameMatcher
     */
    private $useImportNameMatcher;
    public function __construct(\RectorPrefix20211123\Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser $simpleCallableNodeTraverser, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \PHPStan\Reflection\ReflectionProvider $reflectionProvider, \Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder, \Rector\CodingStyle\NodeAnalyzer\UseImportNameMatcher $useImportNameMatcher)
    {
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->reflectionProvider = $reflectionProvider;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->useImportNameMatcher = $useImportNameMatcher;
    }
    // Avoids circular reference
    /**
     * @required
     */
    public function autowireShortNameResolver(\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory $phpDocInfoFactory) : void
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
    }
    /**
     * @return array<string, string>
     */
    public function resolveFromFile(\Rector\Core\ValueObject\Application\File $file) : array
    {
        $filePath = $file->getFilePath();
        if (isset($this->shortNamesByFilePath[$filePath])) {
            return $this->shortNamesByFilePath[$filePath];
        }
        $shortNamesToFullyQualifiedNames = $this->resolveForStmts($file->getNewStmts());
        $this->shortNamesByFilePath[$filePath] = $shortNamesToFullyQualifiedNames;
        return $shortNamesToFullyQualifiedNames;
    }
    /**
     * Collects all "class <SomeClass>", "trait <SomeTrait>" and "interface <SomeInterface>"
     * @return string[]
     */
    public function resolveShortClassLikeNamesForNode(\PhpParser\Node $node) : array
    {
        $namespace = $this->betterNodeFinder->findParentType($node, \PhpParser\Node\Stmt\Namespace_::class);
        if (!$namespace instanceof \PhpParser\Node\Stmt\Namespace_) {
            // only handle namespace nodes
            return [];
        }
        /** @var ClassLike[] $classLikes */
        $classLikes = $this->betterNodeFinder->findInstanceOf($namespace, \PhpParser\Node\Stmt\ClassLike::class);
        $shortClassLikeNames = [];
        foreach ($classLikes as $classLike) {
            $shortClassLikeNames[] = $this->nodeNameResolver->getShortName($classLike);
        }
        return \array_unique($shortClassLikeNames);
    }
    /**
     * @param Stmt[] $stmts
     * @return array<string, string>
     */
    private function resolveForStmts(array $stmts) : array
    {
        $shortNamesToFullyQualifiedNames = [];
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($stmts, function (\PhpParser\Node $node) use(&$shortNamesToFullyQualifiedNames) : void {
            // class name is used!
            if ($node instanceof \PhpParser\Node\Stmt\ClassLike && $node->name instanceof \PhpParser\Node\Identifier) {
                $fullyQualifiedName = $this->nodeNameResolver->getName($node);
                if ($fullyQualifiedName === null) {
                    return;
                }
                $shortNamesToFullyQualifiedNames[$node->name->toString()] = $fullyQualifiedName;
                return;
            }
            if (!$node instanceof \PhpParser\Node\Name) {
                return;
            }
            $originalName = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::ORIGINAL_NAME);
            if (!$originalName instanceof \PhpParser\Node\Name) {
                return;
            }
            // already short
            if (\strpos($originalName->toString(), '\\') !== \false) {
                return;
            }
            $fullyQualifiedName = $this->nodeNameResolver->getName($node);
            $shortNamesToFullyQualifiedNames[$originalName->toString()] = $fullyQualifiedName;
        });
        $docBlockShortNamesToFullyQualifiedNames = $this->resolveFromStmtsDocBlocks($stmts);
        return \array_merge($shortNamesToFullyQualifiedNames, $docBlockShortNamesToFullyQualifiedNames);
    }
    /**
     * @param Stmt[] $stmts
     * @return array<string, string>
     */
    private function resolveFromStmtsDocBlocks(array $stmts) : array
    {
        $reflectionClass = $this->resolveNativeClassReflection($stmts);
        $shortNames = [];
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($stmts, function (\PhpParser\Node $node) use(&$shortNames) : void {
            // speed up for nodes that are
            $phpDocInfo = $this->phpDocInfoFactory->createFromNode($node);
            if (!$phpDocInfo instanceof \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo) {
                return;
            }
            $phpDocNodeTraverser = new \RectorPrefix20211123\Symplify\SimplePhpDocParser\PhpDocNodeTraverser();
            $phpDocNodeTraverser->traverseWithCallable($phpDocInfo->getPhpDocNode(), '', function ($node) use(&$shortNames) {
                if ($node instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode) {
                    $shortName = \trim($node->name, '@');
                    if (\RectorPrefix20211123\Nette\Utils\Strings::match($shortName, self::BIG_LETTER_START_REGEX)) {
                        $shortNames[] = $shortName;
                    }
                    return null;
                }
                if ($node instanceof \PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode) {
                    $shortNames[] = $node->name;
                }
                return null;
            });
        });
        return $this->fqnizeShortNames($shortNames, $reflectionClass, $stmts);
    }
    /**
     * @param Node[] $stmts
     */
    private function resolveNativeClassReflection(array $stmts) : ?\ReflectionClass
    {
        $firstClassLike = $this->betterNodeFinder->findFirstInstanceOf($stmts, \PhpParser\Node\Stmt\ClassLike::class);
        if (!$firstClassLike instanceof \PhpParser\Node\Stmt\ClassLike) {
            return null;
        }
        $className = (string) $this->nodeNameResolver->getName($firstClassLike);
        if (!$this->reflectionProvider->hasClass($className)) {
            return null;
        }
        $classReflection = $this->reflectionProvider->getClass($className);
        return $classReflection->getNativeReflection();
    }
    /**
     * @param string[] $shortNames
     * @param Stmt[] $stmts
     * @return array<string, string>
     */
    private function fqnizeShortNames(array $shortNames, ?\ReflectionClass $reflectionClass, array $stmts) : array
    {
        $shortNamesToFullyQualifiedNames = [];
        foreach ($shortNames as $shortName) {
            if ($reflectionClass instanceof \ReflectionClass) {
                $fullyQualifiedName = \RectorPrefix20211123\Nette\Utils\Reflection::expandClassName($shortName, $reflectionClass);
            } else {
                $fullyQualifiedName = $this->useImportNameMatcher->matchNameWithStmts($shortName, $stmts) ?: $shortName;
            }
            $shortNamesToFullyQualifiedNames[$shortName] = $fullyQualifiedName;
        }
        return $shortNamesToFullyQualifiedNames;
    }
}
