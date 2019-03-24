<?php declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\PhpParser\Node\Resolver\NameResolver;
use Rector\PhpParser\NodeTraverser\CallableNodeTraverser;

final class PhpSpecMockCollector
{
    /**
     * @var mixed[]
     */
    private $mocks = [];

    /**
     * @var NameResolver
     */
    private $nameResolver;

    /**
     * @var CallableNodeTraverser
     */
    private $callableNodeTraverser;

    /**
     * @var mixed[]
     */
    private $mocksWithsTypes = [];

    public function __construct(NameResolver $nameResolver, CallableNodeTraverser $callableNodeTraverser)
    {
        $this->nameResolver = $nameResolver;
        $this->callableNodeTraverser = $callableNodeTraverser;
    }

    /**
     * @return mixed[]
     */
    public function resolveClassMocksFromParam(Class_ $class): array
    {
        $className = $this->nameResolver->resolve($class);

        if (isset($this->mocks[$className])) {
            return $this->mocks[$className];
        }

        $this->callableNodeTraverser->traverseNodesWithCallable((array) $class->stmts, function (Node $node): void {
            if (! $node instanceof ClassMethod) {
                return;
            }

            if (! $node->isPublic()) {
                return;
            }

            foreach ($node->params as $param) {
                $this->addMockFromParam($param);
            }
        });

        // set default value if none was found
        if (! isset($this->mocks[$className])) {
            $this->mocks[$className] = [];
        }

        return $this->mocks[$className];
    }

    public function isVariableMockInProperty(Variable $variable): bool
    {
        $variableName = $this->nameResolver->resolve($variable);
        $methodName = $variable->getAttribute(Attribute::METHOD_NAME);
        $className = $variable->getAttribute(Attribute::CLASS_NAME);

        if (! isset($this->mocks[$className][$variableName])) {
            return false;
        }

        $methodNames = $this->mocks[$className][$variableName];

        if (count($methodNames) <= 1) {
            return false;
        }

        return in_array($methodName, $methodNames, true);
    }

    public function getTypeForClassAndVariable(Class_ $node, string $variable): ?string
    {
        $className = $this->nameResolver->resolve($node);

        return $this->mocksWithsTypes[$className][$variable] ?? null;
    }

    private function addMockFromParam(Param $param): void
    {
        $variable = $this->nameResolver->resolve($param->var);

        /** @var string $class */
        $class = $param->getAttribute(Attribute::CLASS_NAME);

        $this->mocks[$class][$variable][] = $param->getAttribute(Attribute::METHOD_NAME);

        if ($param->type === null) {
            throw new ShouldNotHappenException();
        }

        $paramType = (string) ($param->type->getAttribute('originalName') ?: $param->type);
        $this->mocksWithsTypes[$class][$variable] = $paramType;
    }
}
