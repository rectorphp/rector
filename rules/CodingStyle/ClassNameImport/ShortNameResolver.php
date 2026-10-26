<?php

declare (strict_types=1);
namespace Rector\CodingStyle\ClassNameImport;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\ClassLike;
use PHPStan\PhpDocParser\Ast\NodeTraverser;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\CodingStyle\NodeAnalyzer\UseImportNameMatcher;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser;
use Rector\PhpDocParser\PhpDocParser\PhpDocNodeVisitor\CallablePhpDocNodeVisitor;
use Rector\PhpParser\Node\BetterNodeFinder;
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
    private ?File $shortNamesFile = null;
    private int $shortNamesVersion = 0;
    /**
     * @var array<string, string>
     */
    private array $shortNamesToFullyQualifiedNames = [];
    private ?File $shortClassLikeNamesFile = null;
    private int $shortClassLikeNamesVersion = 0;
    /**
     * @var string[]
     */
    private array $shortClassLikeNames = [];
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
    public function resolveFromFile(File $file): array
    {
        // re-resolve when the file stmts change, so later iterations don't read a stale map
        if ($file === $this->shortNamesFile && $file->getNewStmtsVersion() === $this->shortNamesVersion) {
            return $this->shortNamesToFullyQualifiedNames;
        }
        $this->shortNamesFile = $file;
        $this->shortNamesVersion = $file->getNewStmtsVersion();
        $this->shortNamesToFullyQualifiedNames = $this->resolveForStmts($file->getNewStmts());
        return $this->shortNamesToFullyQualifiedNames;
    }
    /**
     * Collects all "class <SomeClass>", "trait <SomeTrait>" and "interface <SomeInterface>"
     * @return string[]
     */
    public function resolveShortClassLikeNames(File $file): array
    {
        // asked for every imported name, while the class-likes only change with a new version of the file stmts
        if ($file === $this->shortClassLikeNamesFile && $file->getNewStmtsVersion() === $this->shortClassLikeNamesVersion) {
            return $this->shortClassLikeNames;
        }
        $this->shortClassLikeNamesFile = $file;
        $this->shortClassLikeNamesVersion = $file->getNewStmtsVersion();
        $this->shortClassLikeNames = $this->findShortClassLikeNames($file);
        return $this->shortClassLikeNames;
    }
    /**
     * @return string[]
     */
    private function findShortClassLikeNames(File $file): array
    {
        $rootNode = $file->getUseImportsRootNode();
        // nothing to resolve
        if (!$rootNode instanceof Node) {
            return [];
        }
        /** @var ClassLike[] $classLikes */
        $classLikes = $this->betterNodeFinder->findInstanceOf($rootNode->stmts, ClassLike::class);
        $shortClassLikeNames = [];
        foreach ($classLikes as $classLike) {
            $shortClassLikeNames[] = $this->nodeNameResolver->getShortName($classLike);
        }
        return array_unique($shortClassLikeNames);
    }
    /**
     * @param Stmt[] $stmts
     * @return array<string, string>
     */
    private function resolveForStmts(array $stmts): array
    {
        $shortNamesToFullyQualifiedNames = [];
        $docBlockShortNames = [];
        // single traversal collects both PHP names and docblock short names, so the file stmts are walked once
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($stmts, function (Node $node) use (&$shortNamesToFullyQualifiedNames, &$docBlockShortNames) {
            foreach ($this->resolveDocBlockShortNames($node) as $docBlockShortName) {
                $docBlockShortNames[] = $docBlockShortName;
            }
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
            if (strpos($originalName->toString(), '\\') !== \false) {
                return null;
            }
            $shortNamesToFullyQualifiedNames[$originalName->toString()] = $this->nodeNameResolver->getName($node);
            return null;
        });
        $docBlockShortNamesToFullyQualifiedNames = $this->fqnizeShortNames($docBlockShortNames, $stmts);
        /** @var array<string, string> $result */
        $result = array_merge($shortNamesToFullyQualifiedNames, $docBlockShortNamesToFullyQualifiedNames);
        return $result;
    }
    /**
     * @return string[]
     */
    private function resolveDocBlockShortNames(Node $node): array
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNode($node);
        if (!$phpDocInfo instanceof PhpDocInfo) {
            return [];
        }
        $shortNames = [];
        $phpDocNodeTraverser = new NodeTraverser([new CallablePhpDocNodeVisitor(static function ($node) use (&$shortNames) {
            if ($node instanceof PhpDocTagNode) {
                $shortName = trim($node->name, '@');
                if (ucfirst($shortName) === $shortName) {
                    $shortNames[] = $shortName;
                }
                return null;
            }
            if ($node instanceof IdentifierTypeNode) {
                $shortNames[] = $node->name;
            }
            return null;
        })]);
        $phpDocNodeTraverser->traverse([$phpDocInfo->getPhpDocNode()]);
        return $shortNames;
    }
    /**
     * @param string[] $shortNames
     * @param Stmt[] $stmts
     * @return array<string, string>
     */
    private function fqnizeShortNames(array $shortNames, array $stmts): array
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
