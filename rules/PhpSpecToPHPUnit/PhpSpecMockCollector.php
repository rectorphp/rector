<?php

declare (strict_types=1);
namespace Rector\PhpSpecToPHPUnit;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use RectorPrefix20211020\Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;
final class PhpSpecMockCollector
{
    /**
     * @var mixed[]
     */
    private $mocks = [];
    /**
     * @var mixed[]
     */
    private $mocksWithsTypes = [];
    /**
     * @var mixed[]
     */
    private $propertyMocksByClass = [];
    /**
     * @var \Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(\RectorPrefix20211020\Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser $simpleCallableNodeTraverser, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver)
    {
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
        $this->nodeNameResolver = $nodeNameResolver;
    }
    /**
     * @return mixed[]
     */
    public function resolveClassMocksFromParam(\PhpParser\Node\Stmt\Class_ $class) : array
    {
        $className = $this->nodeNameResolver->getName($class);
        if (isset($this->mocks[$className]) && $this->mocks[$className] !== []) {
            return $this->mocks[$className];
        }
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($class, function (\PhpParser\Node $node) : void {
            if (!$node instanceof \PhpParser\Node\Stmt\ClassMethod) {
                return;
            }
            if (!$node->isPublic()) {
                return;
            }
            foreach ($node->params as $param) {
                $this->addMockFromParam($param);
            }
        });
        // set default value if none was found
        if (!isset($this->mocks[$className])) {
            $this->mocks[$className] = [];
        }
        return $this->mocks[$className];
    }
    public function isVariableMockInProperty(\PhpParser\Node\Expr\Variable $variable) : bool
    {
        $variableName = $this->nodeNameResolver->getName($variable);
        $className = $variable->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CLASS_NAME);
        return \in_array($variableName, $this->propertyMocksByClass[$className] ?? [], \true);
    }
    public function getTypeForClassAndVariable(\PhpParser\Node\Stmt\Class_ $class, string $variable) : string
    {
        $className = $this->nodeNameResolver->getName($class);
        if (!isset($this->mocksWithsTypes[$className][$variable])) {
            throw new \Rector\Core\Exception\ShouldNotHappenException();
        }
        return $this->mocksWithsTypes[$className][$variable];
    }
    public function addPropertyMock(string $class, string $property) : void
    {
        $this->propertyMocksByClass[$class][] = $property;
    }
    private function addMockFromParam(\PhpParser\Node\Param $param) : void
    {
        $variable = $this->nodeNameResolver->getName($param->var);
        /** @var string $class */
        $class = $param->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CLASS_NAME);
        $classMethod = $param->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::METHOD_NODE);
        if (!$classMethod instanceof \PhpParser\Node\Stmt\ClassMethod) {
            throw new \Rector\Core\Exception\ShouldNotHappenException();
        }
        $methodName = $this->nodeNameResolver->getName($classMethod);
        $this->mocks[$class][$variable][] = $methodName;
        if ($param->type === null) {
            throw new \Rector\Core\Exception\ShouldNotHappenException();
        }
        $paramType = (string) ($param->type->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::ORIGINAL_NAME) ?: $param->type);
        $this->mocksWithsTypes[$class][$variable] = $paramType;
    }
}
