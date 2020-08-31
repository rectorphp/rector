<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\NodeVisitorAbstract;
use Rector\CodingStyle\Naming\ClassNaming;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class FunctionMethodAndClassNodeVisitor extends NodeVisitorAbstract
{
    /**
     * @var ClassNaming
     */
    private $classNaming;

    /**
     * @var string|null
     */
    private $methodName;

    /**
     * @var string|null
     */
    private $className;

    /**
     * @var string|null
     */
    private $classShortName;

    /**
     * @var ClassLike[]|null[]
     */
    private $classStack = [];

    /**
     * @var ClassMethod[]|null[]
     */
    private $methodStack = [];

    /**
     * @var ClassLike|null
     */
    private $classLike;

    /**
     * @var ClassMethod|null
     */
    private $classMethod;

    /**
     * @var Function_|null
     */
    private $function;

    /**
     * @var Closure|null
     */
    private $closure;

    public function __construct(ClassNaming $classNaming)
    {
        $this->classNaming = $classNaming;
    }

    /**
     * @param Node[] $nodes
     * @return Node[]|null
     */
    public function beforeTraverse(array $nodes): ?array
    {
        $this->classLike = null;
        $this->className = null;
        $this->methodName = null;
        $this->classMethod = null;
        $this->function = null;
        $this->closure = null;

        return null;
    }

    public function enterNode(Node $node): ?Node
    {
        $this->processClass($node);
        $this->processMethod($node);
        $this->processFunction($node);
        $this->processClosure($node);

        return $node;
    }


    public function leaveNode(Node $node)
    {
        if ($node instanceof ClassLike) {
            $classLike = array_pop($this->classStack);
            $this->setClassNodeAndName($classLike);
        }

        if ($node instanceof ClassMethod) {
            $this->classMethod = array_pop($this->methodStack);
            $this->methodName = (string) $this->methodName;
        }

        return null;
    }

    private function processClass(Node $node): void
    {
        if ($node instanceof ClassLike) {
            $this->classStack[] = $this->classLike;
            $this->setClassNodeAndName($node);
        }

        $node->setAttribute(AttributeKey::CLASS_NODE, $this->classLike);
        $node->setAttribute(AttributeKey::CLASS_NAME, $this->className);
        $node->setAttribute(AttributeKey::CLASS_SHORT_NAME, $this->classShortName);

        if ($this->classLike instanceof Class_) {
            $this->setParentClassName($this->classLike, $node);
        }
    }

    private function processMethod(Node $node): void
    {
        if ($node instanceof ClassMethod) {
            $this->methodStack[] = $this->classMethod;

            $this->classMethod = $node;
            $this->methodName = (string) $node->name;
        }

        $node->setAttribute(AttributeKey::METHOD_NAME, $this->methodName);
        $node->setAttribute(AttributeKey::METHOD_NODE, $this->classMethod);
    }

    private function processFunction(Node $node): void
    {
        if ($node instanceof Function_) {
            $this->function = $node;
        }

        $node->setAttribute(AttributeKey::FUNCTION_NODE, $this->function);
    }

    private function processClosure(Node $node): void
    {
        if ($node instanceof Closure) {
            $this->closure = $node;
        }

        $node->setAttribute(AttributeKey::CLOSURE_NODE, $this->closure);
    }

    private function setClassNodeAndName(?ClassLike $classLike): void
    {
        $this->classLike = $classLike;
        if ($classLike === null || $classLike->name === null) {
            $this->className = null;
        } elseif (property_exists($classLike, 'namespacedName')) {
            $this->className = $classLike->namespacedName->toString();
            $this->classShortName = $this->classNaming->getShortName($this->className);
        } else {
            $this->className = (string) $classLike->name;
            $this->classShortName = $this->classNaming->getShortName($this->className);
        }
    }

    private function setParentClassName(Class_ $class, Node $node): void
    {
        if ($class->extends === null) {
            return;
        }

        $parentClassResolvedName = $class->extends->getAttribute(AttributeKey::RESOLVED_NAME);
        if ($parentClassResolvedName instanceof FullyQualified) {
            $parentClassResolvedName = $parentClassResolvedName->toString();
        }

        $node->setAttribute(AttributeKey::PARENT_CLASS_NAME, $parentClassResolvedName);
    }
}
