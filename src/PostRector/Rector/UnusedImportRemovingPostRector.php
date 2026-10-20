<?php

declare (strict_types=1);
namespace Rector\PostRector\Rector;

use RectorPrefix202610\Nette\Utils\Strings;
use PhpParser\Comment;
use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Declare_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Nop;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\UseItem;
use PhpParser\NodeVisitor;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser;
use Rector\PhpParser\Node\FileNode;
use Rector\ValueObject\Application\File;
final class UnusedImportRemovingPostRector extends \Rector\PostRector\Rector\AbstractPostRector
{
    /**
     * @readonly
     */
    private SimpleCallableNodeTraverser $simpleCallableNodeTraverser;
    /**
     * @readonly
     */
    private PhpDocInfoFactory $phpDocInfoFactory;
    private ?File $usedNamesFile = null;
    /**
     * @var array<int, array{version: int, names: string[]}>
     */
    private array $usedNamesCache = [];
    public function __construct(SimpleCallableNodeTraverser $simpleCallableNodeTraverser, PhpDocInfoFactory $phpDocInfoFactory)
    {
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
    }
    public function enterNode(Node $node): ?Node
    {
        if (!$node instanceof Namespace_ && !$node instanceof FileNode) {
            return null;
        }
        // no use import at this level → nothing to remove, skip the full name resolution walks
        if (!$this->hasUseStmt($node->stmts)) {
            return null;
        }
        $hasChanged = \false;
        $namespaceOriginalCase = $node instanceof Namespace_ && $node->name instanceof Name ? $node->name->toString() : null;
        $namesInOriginalCase = $this->resolveUsedPhpAndDocNames($node);
        $namesInLowerCase = array_map(\Closure::fromCallable('strtolower'), $namesInOriginalCase);
        $firstStmtKey = 0;
        foreach ($node->stmts as $key => $stmt) {
            if ($stmt instanceof Declare_ && $key === 0) {
                ++$firstStmtKey;
            }
            if (!$stmt instanceof Use_) {
                continue;
            }
            if ($stmt->uses === [] || $namesInOriginalCase === []) {
                unset($node->stmts[$key]);
                $hasChanged = \true;
                continue;
            }
            $isCaseSensitive = $stmt->type === Use_::TYPE_CONSTANT;
            $names = $isCaseSensitive ? $namesInOriginalCase : $namesInLowerCase;
            $namespaceName = $namespaceOriginalCase === null ? null : ($isCaseSensitive ? $namespaceOriginalCase : strtolower($namespaceOriginalCase));
            foreach ($stmt->uses as $useUseKey => $useUse) {
                if ($this->isUseImportUsed($useUse, $isCaseSensitive, $names, $namespaceName)) {
                    continue;
                }
                unset($stmt->uses[$useUseKey]);
                $hasChanged = \true;
            }
            if ($stmt->uses === []) {
                $comments = $node->stmts[$key]->getComments();
                if ($key === $firstStmtKey && $comments !== []) {
                    $node->stmts[$key] = new Nop();
                    $node->stmts[$key]->setAttribute(AttributeKey::COMMENTS, $comments);
                } else {
                    unset($node->stmts[$key]);
                }
            }
        }
        if ($hasChanged === \false) {
            return null;
        }
        $this->addRectorClassWithLine($node);
        $node->stmts = array_values($node->stmts);
        return $node;
    }
    /**
     * @param Node[] $stmts
     */
    private function hasUseStmt(array $stmts): bool
    {
        $found = \false;
        foreach ($stmts as $stmt) {
            if ($stmt instanceof Use_) {
                $found = \true;
                break;
            }
        }
        return $found;
    }
    /**
     * Single walk that collects both the referenced PHP names and the docblock class names,
     * so the file stmts are traversed once instead of twice.
     *
     * @return string[]
     * @param \PhpParser\Node\Stmt\Namespace_|\Rector\PhpParser\Node\FileNode $rootNode
     */
    private function findUsedNames($rootNode): array
    {
        // the root node's own docblock (the former doc walk traversed the root node, not just its stmts)
        $names = $this->resolveDocBlockNames($rootNode);
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($rootNode->stmts, function (Node $node) use (&$names): ?int {
            // collect the node's docblock first, so a Use_ node's own doc is still read before we stop descending
            $names = array_merge($names, $this->resolveDocBlockNames($node));
            if ($node instanceof Use_) {
                return NodeVisitor::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
            }
            if (!$node instanceof Name) {
                return null;
            }
            if ($node instanceof FullyQualified) {
                $originalName = $node->getAttribute(AttributeKey::ORIGINAL_NAME);
                if ($originalName instanceof Name) {
                    // collect original Name as cover namespaced used
                    $names[] = $originalName->toString();
                    return null;
                }
            }
            $names[] = $node->toString();
            return null;
        });
        return $names;
    }
    /**
     * @return string[]
     */
    private function resolveDocBlockNames(Node $node): array
    {
        $comments = $node->getComments();
        if ($comments === []) {
            return [];
        }
        $docs = array_filter($comments, static fn(Comment $comment): bool => $comment instanceof Doc);
        if ($docs === []) {
            return [];
        }
        $names = [];
        $totalDocs = count($docs);
        foreach ($docs as $doc) {
            $nodeToCheck = $totalDocs === 1 ? $node : clone $node;
            if ($totalDocs > 1) {
                $nodeToCheck->setDocComment($doc);
            }
            $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($nodeToCheck);
            $names = array_merge(is_array($names) ? $names : iterator_to_array(is_array($names) ? new \ArrayIterator($names) : $names), is_array($phpDocInfo->getAnnotationClassNames()) ? $phpDocInfo->getAnnotationClassNames() : iterator_to_array(is_array($phpDocInfo->getAnnotationClassNames()) ? new \ArrayIterator($phpDocInfo->getAnnotationClassNames()) : $phpDocInfo->getAnnotationClassNames()));
            $constFetchNodeNames = $phpDocInfo->getConstFetchNodeClassNames();
            $names = array_merge(is_array($names) ? $names : iterator_to_array(is_array($names) ? new \ArrayIterator($names) : $names), is_array($constFetchNodeNames) ? $constFetchNodeNames : iterator_to_array(is_array($constFetchNodeNames) ? new \ArrayIterator($constFetchNodeNames) : $constFetchNodeNames));
            $genericTagClassNames = $phpDocInfo->getGenericTagClassNames();
            $names = array_merge(is_array($names) ? $names : iterator_to_array(is_array($names) ? new \ArrayIterator($names) : $names), is_array($genericTagClassNames) ? $genericTagClassNames : iterator_to_array(is_array($genericTagClassNames) ? new \ArrayIterator($genericTagClassNames) : $genericTagClassNames));
            $inlineGenericUsesTagClassNames = $phpDocInfo->getInlineGenericUsesTagClassNames();
            $names = array_merge(is_array($names) ? $names : iterator_to_array(is_array($names) ? new \ArrayIterator($names) : $names), is_array($inlineGenericUsesTagClassNames) ? $inlineGenericUsesTagClassNames : iterator_to_array(is_array($inlineGenericUsesTagClassNames) ? new \ArrayIterator($inlineGenericUsesTagClassNames) : $inlineGenericUsesTagClassNames));
            $arrayItemTagClassNames = $phpDocInfo->getArrayItemNodeClassNames();
            $names = array_merge(is_array($names) ? $names : iterator_to_array(is_array($names) ? new \ArrayIterator($names) : $names), is_array($arrayItemTagClassNames) ? $arrayItemTagClassNames : iterator_to_array(is_array($arrayItemTagClassNames) ? new \ArrayIterator($arrayItemTagClassNames) : $arrayItemTagClassNames));
        }
        return $names;
    }
    /**
     * @return string[]
     * @param \PhpParser\Node\Stmt\Namespace_|\Rector\PhpParser\Node\FileNode $rootNode
     */
    private function resolveUsedPhpAndDocNames($rootNode): array
    {
        // the referenced names only change with a new stmts version, while the post-rector chain
        // re-runs this node across convergence iterations, so cache per (file, version)
        $file = $this->getFile();
        if ($file !== $this->usedNamesFile) {
            $this->usedNamesFile = $file;
            $this->usedNamesCache = [];
        }
        $version = $file->getNewStmtsVersion();
        $objectId = spl_object_id($rootNode);
        $cached = $this->usedNamesCache[$objectId] ?? null;
        if ($cached !== null && $cached['version'] === $version) {
            return $cached['names'];
        }
        $names = array_unique($this->findUsedNames($rootNode));
        $this->usedNamesCache[$objectId] = ['version' => $version, 'names' => $names];
        return $names;
    }
    /**
     * @param string[] $names
     */
    private function isUseImportUsed(UseItem $useItem, bool $isCaseSensitive, array $names, ?string $namespaceName): bool
    {
        $comparedName = $useItem->alias instanceof Identifier ? $useItem->alias->toString() : $useItem->name->toString();
        if (!$isCaseSensitive) {
            $comparedName = strtolower($comparedName);
        }
        if (in_array($comparedName, $names, \true)) {
            return \true;
        }
        $lastName = Strings::after($comparedName, '\\', -1);
        $namespacedPrefix = $lastName . '\\';
        if ($namespacedPrefix === '\\') {
            $namespacedPrefix = $comparedName . '\\';
        }
        // match partial import
        foreach ($names as $name) {
            if (strncmp($name, '\\', strlen('\\')) === 0) {
                continue;
            }
            if ($this->isSubNamespace($name, $comparedName, $namespacedPrefix)) {
                return \true;
            }
            if (strncmp($name, $lastName . '\\', strlen($lastName . '\\')) !== 0) {
                if (strncmp($name, $comparedName . '\\', strlen($comparedName . '\\')) === 0) {
                    return \true;
                }
                continue;
            }
            if ($namespaceName === null) {
                return \true;
            }
            if (strncmp($name, $namespaceName . '\\', strlen($namespaceName . '\\')) !== 0) {
                return \true;
            }
        }
        return \false;
    }
    private function isSubNamespace(string $name, string $comparedName, string $namespacedPrefix): bool
    {
        // a partially qualified name like "Foo\Bar" resolves through the import of its first
        // segment, never through an import whose tail it happens to match, so only a single
        // segment name (the imported short name) may be matched against the import's tail here
        if (strpos($name, '\\') === \false && substr_compare($comparedName, '\\' . $name, -strlen('\\' . $name)) === 0) {
            return \true;
        }
        if (strncmp($name, $namespacedPrefix, strlen($namespacedPrefix)) === 0) {
            $subNamespace = (string) substr($name, strlen($namespacedPrefix));
            return strpos($subNamespace, '\\') === \false;
        }
        return \false;
    }
}
