<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Helper;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PHPStan\Type\ObjectType;
use Rector\Core\PhpParser\Node\Value\ValueResolver;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;
final class Typo3NodeResolver
{
    /**
     * @var string
     */
    public const TYPO_SCRIPT_FRONTEND_CONTROLLER = 'TSFE';
    /**
     * @var string
     */
    public const TIME_TRACKER = 'TT';
    /**
     * @var string
     */
    public const PARSETIME_START = 'PARSETIME_START';
    /**
     * @var string
     */
    public const TYPO3_LOADED_EXT = 'TYPO3_LOADED_EXT';
    /**
     * @var string
     */
    public const TYPO3_DB = 'TYPO3_DB';
    /**
     * @var string
     */
    public const BACKEND_USER = 'BE_USER';
    /**
     * @var string
     */
    public const GLOBALS = 'GLOBALS';
    /**
     * @var string
     */
    public const LANG = 'LANG';
    /**
     * @var string
     */
    public const EXEC_TIME = 'EXEC_TIME';
    /**
     * @var string
     */
    public const SIM_EXEC_TIME = 'SIM_EXEC_TIME';
    /**
     * @var string
     */
    public const ACCESS_TIME = 'ACCESS_TIME';
    /**
     * @var string
     */
    public const SIM_ACCESS_TIME = 'SIM_ACCESS_TIME';
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    public function __construct(\Rector\Core\PhpParser\Node\Value\ValueResolver $valueResolver, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\NodeTypeResolver\NodeTypeResolver $nodeTypeResolver)
    {
        $this->valueResolver = $valueResolver;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->nodeTypeResolver = $nodeTypeResolver;
    }
    public function isMethodCallOnGlobals(\PhpParser\Node $node, string $methodCall, string $global) : bool
    {
        if (!$node instanceof \PhpParser\Node\Expr\MethodCall) {
            return \false;
        }
        if (!$node->var instanceof \PhpParser\Node\Expr\ArrayDimFetch) {
            return \false;
        }
        if (!$this->nodeNameResolver->isName($node->name, $methodCall)) {
            return \false;
        }
        if (!$this->nodeNameResolver->isName($node->var->var, self::GLOBALS)) {
            return \false;
        }
        if (null === $node->var->dim) {
            return \false;
        }
        return $this->valueResolver->isValue($node->var->dim, $global);
    }
    public function isAnyMethodCallOnGlobals(\PhpParser\Node $node, string $global) : bool
    {
        if (!$node instanceof \PhpParser\Node\Expr\MethodCall) {
            return \false;
        }
        if (!$node->var instanceof \PhpParser\Node\Expr\ArrayDimFetch) {
            return \false;
        }
        if (!$this->nodeNameResolver->isName($node->var->var, self::GLOBALS)) {
            return \false;
        }
        if (null === $node->var->dim) {
            return \false;
        }
        return $this->valueResolver->isValue($node->var->dim, $global);
    }
    public function isTypo3Global(\PhpParser\Node $node, string $global) : bool
    {
        if (!$node instanceof \PhpParser\Node\Expr\ArrayDimFetch) {
            return \false;
        }
        if ($node->var instanceof \PhpParser\Node\Expr\MethodCall) {
            return \false;
        }
        if (!$this->nodeNameResolver->isName($node->var, self::GLOBALS)) {
            return \false;
        }
        if (null === $node->dim) {
            return \false;
        }
        return $this->valueResolver->isValue($node->dim, $global);
    }
    /**
     * @param string[] $globals
     */
    public function isTypo3Globals(\PhpParser\Node $node, array $globals) : bool
    {
        foreach ($globals as $global) {
            if ($this->isTypo3Global($node, $global)) {
                return \true;
            }
        }
        return \false;
    }
    public function isPropertyFetchOnParentVariableOfTypeTypoScriptFrontendController(\PhpParser\Node $node) : bool
    {
        return $this->isPropertyFetchOnParentVariableOfType($node, 'TypoScriptFrontendController');
    }
    public function isPropertyFetchOnParentVariableOfTypePageRepository(\PhpParser\Node $node) : bool
    {
        return $this->isPropertyFetchOnParentVariableOfType($node, 'PageRepository');
    }
    public function isPropertyFetchOnAnyPropertyOfGlobals(\PhpParser\Node $node, string $global) : bool
    {
        if (!$node instanceof \PhpParser\Node\Expr\PropertyFetch) {
            return \false;
        }
        if (!$node->var instanceof \PhpParser\Node\Expr\ArrayDimFetch) {
            return \false;
        }
        if (!$this->nodeNameResolver->isName($node->var->var, self::GLOBALS)) {
            return \false;
        }
        if (null === $node->var->dim) {
            return \false;
        }
        return $this->valueResolver->isValue($node->var->dim, $global);
    }
    public function isMethodCallOnSysPageOfTSFE(\PhpParser\Node $node) : bool
    {
        return $this->isMethodCallOnPropertyOfGlobals($node, self::TYPO_SCRIPT_FRONTEND_CONTROLLER, 'sys_page');
    }
    public function isMethodCallOnPropertyOfGlobals(\PhpParser\Node $node, string $global, string $property) : bool
    {
        if (!$node instanceof \PhpParser\Node\Expr\MethodCall) {
            return \false;
        }
        if (!$node->var instanceof \PhpParser\Node\Expr\PropertyFetch) {
            return \false;
        }
        if (!$node->var->var instanceof \PhpParser\Node\Expr\ArrayDimFetch) {
            return \false;
        }
        if (!$this->nodeNameResolver->isName($node->var->var->var, self::GLOBALS)) {
            return \false;
        }
        if (!$this->nodeNameResolver->isName($node->var->name, $property)) {
            return \false;
        }
        if (null === $node->var->var->dim) {
            return \false;
        }
        return $this->valueResolver->isValue($node->var->var->dim, $global);
    }
    public function isMethodCallOnBackendUser(\PhpParser\Node $node) : bool
    {
        return $this->isAnyMethodCallOnGlobals($node, self::BACKEND_USER);
    }
    private function isPropertyFetchOnParentVariableOfType(\PhpParser\Node $node, string $type) : bool
    {
        $parentNode = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if (!$parentNode instanceof \PhpParser\Node\Expr\Assign) {
            return \false;
        }
        if (!$parentNode->expr instanceof \PhpParser\Node\Expr\PropertyFetch) {
            return \false;
        }
        $objectType = $this->nodeTypeResolver->getType($parentNode->expr->var);
        if (!$objectType instanceof \PHPStan\Type\ObjectType) {
            return \false;
        }
        return $type === $objectType->getClassName();
    }
}
