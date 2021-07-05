<?php

declare (strict_types=1);
namespace PhpParser;

use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt;
class NameContext
{
    /** @var null|Name Current namespace */
    protected $namespace;
    /** @var Name[][] Map of format [aliasType => [aliasName => originalName]] */
    protected $aliases = [];
    /** @var Name[][] Same as $aliases but preserving original case */
    protected $origAliases = [];
    /** @var ErrorHandler Error handler */
    protected $errorHandler;
    /**
     * Create a name context.
     *
     * @param ErrorHandler $errorHandler Error handling used to report errors
     */
    public function __construct(\PhpParser\ErrorHandler $errorHandler)
    {
        $this->errorHandler = $errorHandler;
    }
    /**
     * Start a new namespace.
     *
     * This also resets the alias table.
     *
     * @param Name|null $namespace Null is the global namespace
     */
    public function startNamespace($namespace = null)
    {
        $this->namespace = $namespace;
        $this->origAliases = $this->aliases = [\PhpParser\Node\Stmt\Use_::TYPE_NORMAL => [], \PhpParser\Node\Stmt\Use_::TYPE_FUNCTION => [], \PhpParser\Node\Stmt\Use_::TYPE_CONSTANT => []];
    }
    /**
     * Add an alias / import.
     *
     * @param Name   $name        Original name
     * @param string $aliasName   Aliased name
     * @param int    $type        One of Stmt\Use_::TYPE_*
     * @param array  $errorAttrs Attributes to use to report an error
     */
    public function addAlias($name, $aliasName, $type, $errorAttrs = [])
    {
        // Constant names are case sensitive, everything else case insensitive
        if ($type === \PhpParser\Node\Stmt\Use_::TYPE_CONSTANT) {
            $aliasLookupName = $aliasName;
        } else {
            $aliasLookupName = \strtolower($aliasName);
        }
        if (isset($this->aliases[$type][$aliasLookupName])) {
            $typeStringMap = [\PhpParser\Node\Stmt\Use_::TYPE_NORMAL => '', \PhpParser\Node\Stmt\Use_::TYPE_FUNCTION => 'function ', \PhpParser\Node\Stmt\Use_::TYPE_CONSTANT => 'const '];
            $this->errorHandler->handleError(new \PhpParser\Error(\sprintf('Cannot use %s%s as %s because the name is already in use', $typeStringMap[$type], $name, $aliasName), $errorAttrs));
            return;
        }
        $this->aliases[$type][$aliasLookupName] = $name;
        $this->origAliases[$type][$aliasName] = $name;
    }
    /**
     * Get current namespace.
     *
     * @return null|Name Namespace (or null if global namespace)
     */
    public function getNamespace()
    {
        return $this->namespace;
    }
    /**
     * Get resolved name.
     *
     * @param Name $name Name to resolve
     * @param int  $type One of Stmt\Use_::TYPE_{FUNCTION|CONSTANT}
     *
     * @return null|Name Resolved name, or null if static resolution is not possible
     */
    public function getResolvedName($name, $type)
    {
        // don't resolve special class names
        if ($type === \PhpParser\Node\Stmt\Use_::TYPE_NORMAL && $name->isSpecialClassName()) {
            if (!$name->isUnqualified()) {
                $this->errorHandler->handleError(new \PhpParser\Error(\sprintf("'\\%s' is an invalid class name", $name->toString()), $name->getAttributes()));
            }
            return $name;
        }
        // fully qualified names are already resolved
        if ($name->isFullyQualified()) {
            return $name;
        }
        // Try to resolve aliases
        if (null !== ($resolvedName = $this->resolveAlias($name, $type))) {
            return $resolvedName;
        }
        if ($type !== \PhpParser\Node\Stmt\Use_::TYPE_NORMAL && $name->isUnqualified()) {
            if (null === $this->namespace) {
                // outside of a namespace unaliased unqualified is same as fully qualified
                return new \PhpParser\Node\Name\FullyQualified($name, $name->getAttributes());
            }
            // Cannot resolve statically
            return null;
        }
        // if no alias exists prepend current namespace
        return \PhpParser\Node\Name\FullyQualified::concat($this->namespace, $name, $name->getAttributes());
    }
    /**
     * Get resolved class name.
     *
     * @param Name $name Class ame to resolve
     *
     * @return Name Resolved name
     */
    public function getResolvedClassName($name) : \PhpParser\Node\Name
    {
        return $this->getResolvedName($name, \PhpParser\Node\Stmt\Use_::TYPE_NORMAL);
    }
    /**
     * Get possible ways of writing a fully qualified name (e.g., by making use of aliases).
     *
     * @param string $name Fully-qualified name (without leading namespace separator)
     * @param int    $type One of Stmt\Use_::TYPE_*
     *
     * @return Name[] Possible representations of the name
     */
    public function getPossibleNames($name, $type) : array
    {
        $lcName = \strtolower($name);
        if ($type === \PhpParser\Node\Stmt\Use_::TYPE_NORMAL) {
            // self, parent and static must always be unqualified
            if ($lcName === "self" || $lcName === "parent" || $lcName === "static") {
                return [new \PhpParser\Node\Name($name)];
            }
        }
        // Collect possible ways to write this name, starting with the fully-qualified name
        $possibleNames = [new \PhpParser\Node\Name\FullyQualified($name)];
        if (null !== ($nsRelativeName = $this->getNamespaceRelativeName($name, $lcName, $type))) {
            // Make sure there is no alias that makes the normally namespace-relative name
            // into something else
            if (null === $this->resolveAlias($nsRelativeName, $type)) {
                $possibleNames[] = $nsRelativeName;
            }
        }
        // Check for relevant namespace use statements
        foreach ($this->origAliases[\PhpParser\Node\Stmt\Use_::TYPE_NORMAL] as $alias => $orig) {
            $lcOrig = $orig->toLowerString();
            if (0 === \strpos($lcName, $lcOrig . '\\')) {
                $possibleNames[] = new \PhpParser\Node\Name($alias . \substr($name, \strlen($lcOrig)));
            }
        }
        // Check for relevant type-specific use statements
        foreach ($this->origAliases[$type] as $alias => $orig) {
            if ($type === \PhpParser\Node\Stmt\Use_::TYPE_CONSTANT) {
                // Constants are are complicated-sensitive
                $normalizedOrig = $this->normalizeConstName($orig->toString());
                if ($normalizedOrig === $this->normalizeConstName($name)) {
                    $possibleNames[] = new \PhpParser\Node\Name($alias);
                }
            } else {
                // Everything else is case-insensitive
                if ($orig->toLowerString() === $lcName) {
                    $possibleNames[] = new \PhpParser\Node\Name($alias);
                }
            }
        }
        return $possibleNames;
    }
    /**
     * Get shortest representation of this fully-qualified name.
     *
     * @param string $name Fully-qualified name (without leading namespace separator)
     * @param int    $type One of Stmt\Use_::TYPE_*
     *
     * @return Name Shortest representation
     */
    public function getShortName($name, $type) : \PhpParser\Node\Name
    {
        $possibleNames = $this->getPossibleNames($name, $type);
        // Find shortest name
        $shortestName = null;
        $shortestLength = \INF;
        foreach ($possibleNames as $possibleName) {
            $length = \strlen($possibleName->toCodeString());
            if ($length < $shortestLength) {
                $shortestName = $possibleName;
                $shortestLength = $length;
            }
        }
        return $shortestName;
    }
    private function resolveAlias(\PhpParser\Node\Name $name, $type)
    {
        $firstPart = $name->getFirst();
        if ($name->isQualified()) {
            // resolve aliases for qualified names, always against class alias table
            $checkName = \strtolower($firstPart);
            if (isset($this->aliases[\PhpParser\Node\Stmt\Use_::TYPE_NORMAL][$checkName])) {
                $alias = $this->aliases[\PhpParser\Node\Stmt\Use_::TYPE_NORMAL][$checkName];
                return \PhpParser\Node\Name\FullyQualified::concat($alias, $name->slice(1), $name->getAttributes());
            }
        } elseif ($name->isUnqualified()) {
            // constant aliases are case-sensitive, function aliases case-insensitive
            $checkName = $type === \PhpParser\Node\Stmt\Use_::TYPE_CONSTANT ? $firstPart : \strtolower($firstPart);
            if (isset($this->aliases[$type][$checkName])) {
                // resolve unqualified aliases
                return new \PhpParser\Node\Name\FullyQualified($this->aliases[$type][$checkName], $name->getAttributes());
            }
        }
        // No applicable aliases
        return null;
    }
    private function getNamespaceRelativeName(string $name, string $lcName, int $type)
    {
        if (null === $this->namespace) {
            return new \PhpParser\Node\Name($name);
        }
        if ($type === \PhpParser\Node\Stmt\Use_::TYPE_CONSTANT) {
            // The constants true/false/null always resolve to the global symbols, even inside a
            // namespace, so they may be used without qualification
            if ($lcName === "true" || $lcName === "false" || $lcName === "null") {
                return new \PhpParser\Node\Name($name);
            }
        }
        $namespacePrefix = \strtolower($this->namespace . '\\');
        if (0 === \strpos($lcName, $namespacePrefix)) {
            return new \PhpParser\Node\Name(\substr($name, \strlen($namespacePrefix)));
        }
        return null;
    }
    private function normalizeConstName(string $name)
    {
        $nsSep = \strrpos($name, '\\');
        if (\false === $nsSep) {
            return $name;
        }
        // Constants have case-insensitive namespace and case-sensitive short-name
        $ns = \substr($name, 0, $nsSep);
        $shortName = \substr($name, $nsSep + 1);
        return \strtolower($ns) . '\\' . $shortName;
    }
}
