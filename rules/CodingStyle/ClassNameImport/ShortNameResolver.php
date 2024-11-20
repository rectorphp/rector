<?php

declare (strict_types=1);
namespace Rector\CodingStyle\ClassNameImport;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Namespace_;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\CodingStyle\NodeAnalyzer\UseImportNameMatcher;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser;
use Rector\PhpDocParser\PhpDocParser\PhpDocNodeTraverser;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\PhpParser\Node\CustomNode\FileWithoutNamespace;
use Rector\ValueObject\Application\File;
/**
 * @see \Rector\Tests\CodingStyle\ClassNameImport\ShortNameResolver\ShortNameResolverTest
 */
final class ShortNameResolver
{
    /**
     * @readonly
     */
    private SimpleCallableNodeTraverser $simpleCallableNodeTraverser;
    /**
     * @readonly
     */
    private NodeNameResolver $nodeNameResolver;
    /**
     * @readonly
     */
    private BetterNodeFinder $betterNodeFinder;
    /**
     * @readonly
     */
    private UseImportNameMatcher $useImportNameMatcher;
    /**
     * @readonly
     */
    private PhpDocInfoFactory $phpDocInfoFactory;
    /**
     * @var array<string, string[]>
     */
    private array $shortNamesByFilePath = [];
    public function __construct(SimpleCallableNodeTraverser $simpleCallableNodeTraverser, NodeNameResolver $nodeNameResolver, BetterNodeFinder $betterNodeFinder, UseImportNameMatcher $useImportNameMatcher, PhpDocInfoFactory $phpDocInfoFactory)
    {
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->useImportNameMatcher = $useImportNameMatcher;
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
    public function resolveShortClassLikeNames(File $file) : array
    {
        $newStmts = $file->getNewStmts();
        /** @var Namespace_[]|FileWithoutNamespace[] $namespaces */
        $namespaces = \array_filter($newStmts, static fn(Stmt $stmt): bool => $stmt instanceof Namespace_ || $stmt instanceof FileWithoutNamespace);
        if (\count($namespaces) !== 1) {
            // only handle single namespace nodes
            return [];
        }
        $namespace = \current($namespaces);
        /** @var ClassLike[] $classLikes */
        $classLikes = $this->betterNodeFinder->findInstanceOf($namespace->stmts, ClassLike::class);
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
                    if (\ucfirst($shortName) === $shortName) {
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
        return $this->fqnizeShortNames($shortNames, $stmts);
    }
    /**
     * @param string[] $shortNames
     * @param Stmt[] $stmts
     * @return array<string, string>
     */
    private function fqnizeShortNames(array $shortNames, array $stmts) : array
    {
        $shortNamesToFullyQualifiedNames = [];
        foreach ($shortNames as $shortName) {
            $stmtsMatchedName = $this->useImportNameMatcher->matchNameWithStmts($shortName, $stmts);
            if ($stmtsMatchedName == null) {
                continue;
            }
            $shortNamesToFullyQualifiedNames[$shortName] = $stmtsMatchedName;
        }
        return $shortNamesToFullyQualifiedNames;
    }
}
