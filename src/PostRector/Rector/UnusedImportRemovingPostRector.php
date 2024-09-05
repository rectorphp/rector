<?php

declare (strict_types=1);
namespace Rector\PostRector\Rector;

use RectorPrefix202409\Nette\Utils\Strings;
use PhpParser\Comment;
use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;
use PhpParser\NodeTraverser;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser;
use Rector\PhpParser\Node\CustomNode\FileWithoutNamespace;
final class UnusedImportRemovingPostRector extends \Rector\PostRector\Rector\AbstractPostRector
{
    /**
     * @readonly
     * @var \Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    public function __construct(SimpleCallableNodeTraverser $simpleCallableNodeTraverser, PhpDocInfoFactory $phpDocInfoFactory)
    {
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
    }
    public function enterNode(Node $node) : ?Node
    {
        if (!$node instanceof Namespace_ && !$node instanceof FileWithoutNamespace) {
            return null;
        }
        $hasChanged = \false;
        $namespaceName = $node instanceof Namespace_ && $node->name instanceof Name ? $node->name : null;
        $names = $this->resolveUsedPhpAndDocNames($node);
        foreach ($node->stmts as $key => $namespaceStmt) {
            if (!$namespaceStmt instanceof Use_) {
                continue;
            }
            if ($namespaceStmt->uses === []) {
                unset($node->stmts[$key]);
                $hasChanged = \true;
                continue;
            }
            $useUse = $namespaceStmt->uses[0];
            if ($this->isUseImportUsed($useUse, $names, $namespaceName)) {
                continue;
            }
            unset($node->stmts[$key]);
            $hasChanged = \true;
        }
        if ($hasChanged === \false) {
            return null;
        }
        $node->stmts = \array_values($node->stmts);
        return $node;
    }
    /**
     * @return string[]
     * @param \PhpParser\Node\Stmt\Namespace_|\Rector\PhpParser\Node\CustomNode\FileWithoutNamespace $namespace
     */
    private function findNonUseImportNames($namespace) : array
    {
        $names = [];
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($namespace->stmts, static function (Node $node) use(&$names) {
            if ($node instanceof Use_) {
                return NodeTraverser::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
            }
            if (!$node instanceof Name) {
                return null;
            }
            if ($node instanceof FullyQualified) {
                $originalName = $node->getAttribute(AttributeKey::ORIGINAL_NAME);
                if ($originalName instanceof Name) {
                    // collect original Name as cover namespaced used
                    $names[] = $originalName->toString();
                    return $node;
                }
            }
            $names[] = $node->toString();
            return $node;
        });
        return $names;
    }
    /**
     * @return string[]
     * @param \PhpParser\Node\Stmt\Namespace_|\Rector\PhpParser\Node\CustomNode\FileWithoutNamespace $namespace
     */
    private function findNamesInDocBlocks($namespace) : array
    {
        $names = [];
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($namespace, function (Node $node) use(&$names) {
            $comments = $node->getComments();
            if ($comments === []) {
                return null;
            }
            $docs = \array_filter($comments, static function (Comment $comment) : bool {
                return $comment instanceof Doc;
            });
            if ($docs === []) {
                return null;
            }
            $totalDocs = \count($docs);
            foreach ($docs as $doc) {
                $nodeToCheck = $totalDocs === 1 ? $node : clone $node;
                if ($totalDocs > 1) {
                    $nodeToCheck->setDocComment($doc);
                }
                $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($nodeToCheck);
                $names = \array_merge($names, $phpDocInfo->getAnnotationClassNames());
                $constFetchNodeNames = $phpDocInfo->getConstFetchNodeClassNames();
                $names = \array_merge($names, $constFetchNodeNames);
                $genericTagClassNames = $phpDocInfo->getGenericTagClassNames();
                $names = \array_merge($names, $genericTagClassNames);
                $arrayItemTagClassNames = $phpDocInfo->getArrayItemNodeClassNames();
                $names = \array_merge($names, $arrayItemTagClassNames);
            }
        });
        return $names;
    }
    /**
     * @return string[]
     * @param \PhpParser\Node\Stmt\Namespace_|\Rector\PhpParser\Node\CustomNode\FileWithoutNamespace $namespace
     */
    private function resolveUsedPhpAndDocNames($namespace) : array
    {
        $phpNames = $this->findNonUseImportNames($namespace);
        $docBlockNames = $this->findNamesInDocBlocks($namespace);
        $names = \array_merge($phpNames, $docBlockNames);
        return \array_unique($names);
    }
    private function resolveAliasName(UseUse $useUse) : ?string
    {
        return $useUse->alias instanceof Identifier ? $useUse->alias->toString() : null;
    }
    /**
     * @param string[]  $names
     */
    private function isUseImportUsed(UseUse $useUse, array $names, ?Name $namespaceName) : bool
    {
        $comparedName = $useUse->alias instanceof Identifier ? $useUse->alias->toString() : $useUse->name->toString();
        if (\in_array($comparedName, $names, \true)) {
            return \true;
        }
        $namespacedPrefix = Strings::after($comparedName, '\\', -1) . '\\';
        if ($namespacedPrefix === '\\') {
            $namespacedPrefix = $comparedName . '\\';
        }
        $alias = $this->resolveAliasName($useUse);
        $lastName = $useUse->name->getLast();
        $namespaceName = $namespaceName instanceof Name ? $namespaceName->toString() : null;
        // match partial import
        foreach ($names as $name) {
            if ($this->isSubNamespace($name, $comparedName, $namespacedPrefix)) {
                return \true;
            }
            if (\is_string($alias) && $this->isUsedAlias($alias, $name)) {
                return \true;
            }
            if (\strncmp($name, $lastName . '\\', \strlen($lastName . '\\')) !== 0) {
                continue;
            }
            if ($namespaceName === null) {
                return \true;
            }
            if (\strncmp($name, $namespaceName . '\\', \strlen($namespaceName . '\\')) !== 0) {
                return \true;
            }
        }
        return \false;
    }
    private function isUsedAlias(string $alias, string $name) : bool
    {
        if ($alias === $name) {
            return \true;
        }
        if (\strpos($name, '\\') === \false) {
            return \false;
        }
        $namePrefix = Strings::before($name, '\\', 1);
        return $alias === $namePrefix;
    }
    private function isSubNamespace(string $name, string $comparedName, string $namespacedPrefix) : bool
    {
        if (\substr_compare($comparedName, '\\' . $name, -\strlen('\\' . $name)) === 0) {
            return \true;
        }
        if (\strncmp($name, $namespacedPrefix, \strlen($namespacedPrefix)) === 0) {
            $subNamespace = \substr($name, \strlen($namespacedPrefix));
            return \strpos($subNamespace, '\\') === \false;
        }
        return \false;
    }
}
