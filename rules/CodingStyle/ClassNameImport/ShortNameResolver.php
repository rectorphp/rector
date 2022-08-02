<?php

declare (strict_types=1);
namespace Rector\CodingStyle\ClassNameImport;

use RectorPrefix202208\Nette\Utils\Reflection;
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
use Rector\Core\Util\StringUtils;
use Rector\Core\ValueObject\Application\File;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use ReflectionClass;
use RectorPrefix202208\Symfony\Contracts\Service\Attribute\Required;
use RectorPrefix202208\Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;
use RectorPrefix202208\Symplify\Astral\PhpDocParser\PhpDocNodeTraverser;
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
     * @var array<string, string[]>
     */
    private $shortNamesByFilePath = [];
    /**
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    /**
     * @readonly
     * @var \Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\CodingStyle\NodeAnalyzer\UseImportNameMatcher
     */
    private $useImportNameMatcher;
    public function __construct(SimpleCallableNodeTraverser $simpleCallableNodeTraverser, NodeNameResolver $nodeNameResolver, ReflectionProvider $reflectionProvider, BetterNodeFinder $betterNodeFinder, UseImportNameMatcher $useImportNameMatcher)
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
    public function autowire(PhpDocInfoFactory $phpDocInfoFactory) : void
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
    }
    /**
     * @return array<string, string>
     */
    public function resolveFromFile(File $file) : array
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
    public function resolveShortClassLikeNamesForNode(Node $node) : array
    {
        $namespace = $this->betterNodeFinder->findParentType($node, Namespace_::class);
        if (!$namespace instanceof Namespace_) {
            // only handle namespace nodes
            return [];
        }
        /** @var ClassLike[] $classLikes */
        $classLikes = $this->betterNodeFinder->findInstanceOf($namespace, ClassLike::class);
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
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($stmts, function (Node $node) use(&$shortNamesToFullyQualifiedNames) {
            // class name is used!
            if ($node instanceof ClassLike && $node->name instanceof Identifier) {
                $fullyQualifiedName = $this->nodeNameResolver->getName($node);
                if ($fullyQualifiedName === null) {
                    return null;
                }
                $shortNamesToFullyQualifiedNames[$node->name->toString()] = $fullyQualifiedName;
                return null;
            }
            if (!$node instanceof Name) {
                return null;
            }
            $originalName = $node->getAttribute(AttributeKey::ORIGINAL_NAME);
            if (!$originalName instanceof Name) {
                return null;
            }
            // already short
            if (\strpos($originalName->toString(), '\\') !== \false) {
                return null;
            }
            $shortNamesToFullyQualifiedNames[$originalName->toString()] = $this->nodeNameResolver->getName($node);
            return null;
        });
        $docBlockShortNamesToFullyQualifiedNames = $this->resolveFromStmtsDocBlocks($stmts);
        /** @var array<string, string> $result */
        $result = \array_merge($shortNamesToFullyQualifiedNames, $docBlockShortNamesToFullyQualifiedNames);
        return $result;
    }
    /**
     * @param Stmt[] $stmts
     * @return array<string, string>
     */
    private function resolveFromStmtsDocBlocks(array $stmts) : array
    {
        $reflectionClass = $this->resolveNativeClassReflection($stmts);
        $shortNames = [];
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($stmts, function (Node $node) use(&$shortNames) {
            // speed up for nodes that are
            $phpDocInfo = $this->phpDocInfoFactory->createFromNode($node);
            if (!$phpDocInfo instanceof PhpDocInfo) {
                return null;
            }
            $phpDocNodeTraverser = new PhpDocNodeTraverser();
            $phpDocNodeTraverser->traverseWithCallable($phpDocInfo->getPhpDocNode(), '', static function ($node) use(&$shortNames) {
                if ($node instanceof PhpDocTagNode) {
                    $shortName = \trim($node->name, '@');
                    if (StringUtils::isMatch($shortName, self::BIG_LETTER_START_REGEX)) {
                        $shortNames[] = $shortName;
                    }
                    return null;
                }
                if ($node instanceof IdentifierTypeNode) {
                    $shortNames[] = $node->name;
                }
                return null;
            });
            return null;
        });
        return $this->fqnizeShortNames($shortNames, $reflectionClass, $stmts);
    }
    /**
     * @param Node[] $stmts
     */
    private function resolveNativeClassReflection(array $stmts) : ?ReflectionClass
    {
        $firstClassLike = $this->betterNodeFinder->findFirstInstanceOf($stmts, ClassLike::class);
        if (!$firstClassLike instanceof ClassLike) {
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
    private function fqnizeShortNames(array $shortNames, ?ReflectionClass $reflectionClass, array $stmts) : array
    {
        $shortNamesToFullyQualifiedNames = [];
        foreach ($shortNames as $shortName) {
            $stmtsMatchedName = $this->useImportNameMatcher->matchNameWithStmts($shortName, $stmts);
            if ($reflectionClass instanceof ReflectionClass) {
                $fullyQualifiedName = Reflection::expandClassName($shortName, $reflectionClass);
            } elseif (\is_string($stmtsMatchedName)) {
                $fullyQualifiedName = $stmtsMatchedName;
            } else {
                $fullyQualifiedName = $shortName;
            }
            $shortNamesToFullyQualifiedNames[$shortName] = $fullyQualifiedName;
        }
        return $shortNamesToFullyQualifiedNames;
    }
}
