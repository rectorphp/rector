<?php

declare (strict_types=1);
namespace Rector\PostRector\Rector;

use RectorPrefix202312\Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;
use PhpParser\NodeTraverser;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Core\Configuration\Option;
use Rector\Core\Configuration\Parameter\SimpleParameterProvider;
use Rector\Core\PhpParser\Node\CustomNode\FileWithoutNamespace;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser;
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
        if (!SimpleParameterProvider::provideBoolParameter(Option::REMOVE_UNUSED_IMPORTS)) {
            return null;
        }
        if (!$node instanceof Namespace_ && !$node instanceof FileWithoutNamespace) {
            return null;
        }
        $hasChanged = \false;
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
            if ($this->isUseImportUsed($useUse, $names)) {
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
     * @param \PhpParser\Node\Stmt\Namespace_|\Rector\Core\PhpParser\Node\CustomNode\FileWithoutNamespace $namespace
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
            $names[] = $node->toString();
            if ($node instanceof FullyQualified) {
                $originalName = $node->getAttribute(AttributeKey::ORIGINAL_NAME);
                if ($originalName instanceof Name) {
                    // collect original Name as well to cover namespaced used
                    $names[] = $originalName->toString();
                }
            }
            return $node;
        });
        return $names;
    }
    /**
     * @return string[]
     * @param \PhpParser\Node\Stmt\Namespace_|\Rector\Core\PhpParser\Node\CustomNode\FileWithoutNamespace $namespace
     */
    private function findNamesInDocBlocks($namespace) : array
    {
        $names = [];
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($namespace, function (Node $node) use(&$names) {
            if (!$node->hasAttribute(AttributeKey::COMMENTS)) {
                return null;
            }
            $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
            $names = \array_merge($names, $phpDocInfo->getAnnotationClassNames());
            $constFetchNodeNames = $phpDocInfo->getConstFetchNodeClassNames();
            $names = \array_merge($names, $constFetchNodeNames);
            $genericTagClassNames = $phpDocInfo->getGenericTagClassNames();
            $names = \array_merge($names, $genericTagClassNames);
        });
        return $names;
    }
    /**
     * @return string[]
     * @param \PhpParser\Node\Stmt\Namespace_|\Rector\Core\PhpParser\Node\CustomNode\FileWithoutNamespace $namespace
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
    private function isUseImportUsed(UseUse $useUse, array $names) : bool
    {
        $comparedName = $useUse->name->toString();
        if (\in_array($comparedName, $names, \true)) {
            return \true;
        }
        $namespacedPrefix = Strings::after($comparedName, '\\', -1) . '\\';
        if ($namespacedPrefix === '\\') {
            $namespacedPrefix = $comparedName . '\\';
        }
        $alias = $this->resolveAliasName($useUse);
        // match partial import
        foreach ($names as $name) {
            if (\substr_compare($comparedName, '\\' . $name, -\strlen('\\' . $name)) === 0) {
                return \true;
            }
            if ($this->isSubNamespace($name, $namespacedPrefix)) {
                return \true;
            }
            if (!\is_string($alias)) {
                continue;
            }
            if ($alias === $name) {
                return \true;
            }
            if (\strpos($name, '\\') === \false) {
                continue;
            }
            $namePrefix = Strings::before($name, '\\', 1);
            if ($alias === $namePrefix) {
                return \true;
            }
        }
        return \false;
    }
    private function isSubNamespace(string $name, string $namespacedPrefix) : bool
    {
        if (\strncmp($name, $namespacedPrefix, \strlen($namespacedPrefix)) === 0) {
            $subNamespace = \substr($name, \strlen($namespacedPrefix));
            return \strpos($subNamespace, '\\') === \false;
        }
        return \false;
    }
}
