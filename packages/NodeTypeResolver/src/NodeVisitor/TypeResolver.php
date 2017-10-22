<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\NodeVisitorAbstract;
use Rector\BetterReflection\Reflector\MethodReflector;
use Rector\Exception\NotImplementedException;
use Rector\Node\Attribute;
use Rector\NodeAnalyzer\ClassAnalyzer;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\NodeTypeResolver\TypeContext;

/**
 * Inspired by https://github.com/nikic/PHP-Parser/blob/9373a8e9f551516bc8e42aedeacd1b4f635d27fc/lib/PhpParser/NodeVisitor/NameResolver.php.
 */
final class TypeResolver extends NodeVisitorAbstract
{
    /**
     * @var TypeContext
     */
    private $typeContext;

    /**
     * @var callable[]
     */
    private $perNodeResolvers = [];

    /**
     * @var MethodReflector
     */
    private $methodReflector;

    /**
     * @var ClassAnalyzer
     */
    private $classAnalyzer;

    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    public function __construct(
        TypeContext $typeContext,
        MethodReflector $methodReflector,
        ClassAnalyzer $classAnalyzer,
        NodeTypeResolver $nodeTypeResolver
    ) {
        $this->typeContext = $typeContext;

        // add subtypes for PropertyFetch
        $this->perNodeResolvers[PropertyFetch::class] = function (PropertyFetch $propertyFetchNode): void {
            $this->processPropertyFetch($propertyFetchNode);
        };

        $this->methodReflector = $methodReflector;
        $this->classAnalyzer = $classAnalyzer;
        $this->nodeTypeResolver = $nodeTypeResolver;
    }

    /**
     * @param Node[] $nodes
     */
    public function beforeTraverse(array $nodes): void
    {
        $this->typeContext->startFile();
    }

    public function enterNode(Node $node): void
    {
        if ($node instanceof ClassLike) {
            $this->typeContext->enterClass($node);

            return;
        }

        if ($node instanceof FunctionLike) {
            $this->typeContext->enterFunction($node);

            return;
        }

        $nodeClass = get_class($node);

        if (isset($this->perNodeResolvers[$nodeClass])) {
            $this->perNodeResolvers[$nodeClass]($node);
        }

        $type = $this->nodeTypeResolver->resolve($node);
        if ($type) {
            $node->setAttribute(Attribute::TYPE, $type);
        }
    }

    private function getTypeFromNewNode(New_ $newNode): ?string
    {
        // e.g. new class extends AnotherClass();

        if ($this->classAnalyzer->isAnonymousClassNode($newNode->class)) {
            $classNode = $newNode->class;
            if (! $classNode->extends instanceof Name) {
                return null;
            }

            // @todo: add interfaces as well
            // use some gettypesForClass($class) method, already used somewhere else

            $resolvedName = $classNode->extends->getAttribute(Attribute::RESOLVED_NAME);
            if ($resolvedName instanceof FullyQualified) {
                return $resolvedName->toString();
            }

            return null;
        }

        // e.g. new $someClass;
        if ($newNode->class instanceof Variable) {
            // can be anything (dynamic)
            $variableName = $newNode->class->name;

            return $this->typeContext->getTypeForVariable($variableName);
        }

        // e.g. new SomeClass;
        if ($newNode->class instanceof Name) {
            /** @var FullyQualified $fqnName */
            $fqnName = $newNode->class->getAttribute(Attribute::RESOLVED_NAME);

            return $fqnName->toString();
        }

        // e.g. new $this->templateClass;
        if ($newNode->class instanceof PropertyFetch) {
            if ($newNode->class->var->name !== 'this') {
                throw new NotImplementedException(sprintf(
                    'Not implemented yet. Go to "%s()" and add check for "%s" node for external dependency.',
                    __METHOD__,
                    get_class($newNode->class)
                ));
            }

            // can be anything (dynamic)
            $propertyName = (string) $newNode->class->name;

            return $this->typeContext->getTypeForProperty($propertyName);
        }

        throw new NotImplementedException(sprintf(
            'Not implemented yet. Go to "%s()" and add check for "%s" node.',
            __METHOD__,
            get_class($newNode->class)
        ));
    }

    private function processPropertyFetch(PropertyFetch $propertyFetchNode): void
    {
        // e.g. $r->getParameters()[0]->name
        if ($propertyFetchNode->var instanceof ArrayDimFetch) {
            $this->processArrayDimMethodCall($propertyFetchNode);

            return;
        }

        if ($propertyFetchNode->var instanceof New_) {
            $propertyType = $propertyFetchNode->var->class->toString();
            $propertyFetchNode->setAttribute(Attribute::TYPE, $propertyType);

            return;
        }

        if ($propertyFetchNode->var->name !== 'this') {
            return;
        }

        $propertyName = $this->resolvePropertyName($propertyFetchNode);
        $propertyType = $this->typeContext->getTypeForProperty($propertyName);

        if ($propertyType) {
            $propertyFetchNode->setAttribute(Attribute::TYPE, $propertyType);
        }
    }

    private function processArrayDimMethodCall(PropertyFetch $propertyFetchNode): void
    {
        if ($propertyFetchNode->var->var instanceof MethodCall) {
            /** @var Variable $variableNode */
            $variableNode = $propertyFetchNode->var->var->var;
            $variableName = $variableNode->name;
            $variableType = $this->typeContext->getTypeForVariable($variableName);

            $variableNode->setAttribute(Attribute::TYPE, $variableType);
        }
    }

    private function resolvePropertyName(PropertyFetch $propertyFetchNode): string
    {
        if ($propertyFetchNode->name instanceof Variable) {
            return $propertyFetchNode->name->name;
        }

        if ($propertyFetchNode->name instanceof Concat) {
            return '';
        }

        return (string) $propertyFetchNode->name;
    }

    /**
     * Dummy static method call return type that doesn't depend on class reflection.
     */
    private function fallbackStaticType(string $type, string $methodName): ?string
    {
        if ($type === 'Nette\Config\Configurator' && $methodName === 'createContainer') {
            return 'Nette\DI\Container';
        }

        return null;
    }

    private function getMethodReturnType(string $methodCallVariableType, string $methodCallName): ?string
    {
        $methodReflection = $this->methodReflector->reflectClassMethod($methodCallVariableType, $methodCallName);

        if ($methodReflection) {
            $returnType = $methodReflection->getReturnType();
            if ($returnType) {
                return (string) $returnType;
            }
        }

        return $this->fallbackStaticType($methodCallVariableType, $methodCallName);
    }

    private function resolveMethodCallName(Assign $assignNode): ?string
    {
        if ($assignNode->expr->name instanceof Variable) {
            return $assignNode->expr->name->name;
        }

        if ($assignNode->expr->name instanceof PropertyFetch) {
            // not implemented yet
            return null;
        }

        return (string) $assignNode->expr->name;
    }
}
