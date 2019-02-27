<?php declare(strict_types=1);

namespace Rector\Php\Rector\FunctionLike;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\PhpParser\Node\Maintainer\ClassMaintainer;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see https://wiki.php.net/rfc/remove_php4_constructors
 */
final class Php4ConstructorRector extends AbstractRector
{
    /**
     * @var ClassMaintainer
     */
    private $classMaintainer;

    public function __construct(ClassMaintainer $classMaintainer)
    {
        $this->classMaintainer = $classMaintainer;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Changes PHP 4 style constructor to __construct.',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public function SomeClass()
    {
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public function __construct()
    {
    }
}
CODE_SAMPLE
                ),
            ]
        );
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class];
    }

    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }

        $classNode = $node->getAttribute(Attribute::CLASS_NODE);
        if (! $classNode instanceof Class_) {
            return null;
        }

        // process parent call references first
        $this->processClassMethodStatementsForParentConstructorCalls($node);

        // not PSR-4 constructor
        if (! $this->isNameInsensitive($classNode, $this->getName($node))) {
            return null;
        }

        // does it already have a __construct method?
        if (! $this->classMaintainer->hasClassMethod($classNode, '__construct')) {
            $node->name = new Identifier('__construct');
        }

        if ($node->stmts === null) {
            return null;
        }

        if (count($node->stmts) === 1) {
            /** @var Expression $stmt */
            $stmt = $node->stmts[0];

            if ($this->isThisConstructCall($stmt->expr)) {
                $this->removeNode($node);

                return null;
            }
        }

        return $node;
    }

    private function shouldSkip(ClassMethod $classMethod): bool
    {
        $namespace = $classMethod->getAttribute(Attribute::NAMESPACE_NAME);
        // catch only classes without namespace
        if ($namespace !== null) {
            return true;
        }

        if ($classMethod->isAbstract() || $classMethod->isStatic()) {
            return true;
        }

        $classNode = $classMethod->getAttribute(Attribute::CLASS_NODE);
        return $classNode instanceof Class_ && $classNode->name === null;
    }

    private function processClassMethodStatementsForParentConstructorCalls(ClassMethod $classMethod): void
    {
        if (! is_iterable($classMethod->stmts)) {
            return;
        }

        /** @var Expression $methodStmt */
        foreach ($classMethod->stmts as $methodStmt) {
            if ($methodStmt instanceof Expression) {
                $methodStmt = $methodStmt->expr;
            }

            if (! $methodStmt instanceof StaticCall) {
                continue;
            }

            $this->processParentPhp4ConstructCall($methodStmt);
        }
    }

    private function isThisConstructCall(Node $node): bool
    {
        if (! $node instanceof MethodCall) {
            return false;
        }

        if (! $node->var instanceof Variable) {
            return false;
        }

        if (! $this->isName($node->var, 'this')) {
            return false;
        }

        return $this->isName($node, '__construct');
    }

    private function processParentPhp4ConstructCall(Node $node): void
    {
        $parentClassName = $node->getAttribute(Attribute::PARENT_CLASS_NAME);

        if (! $node instanceof StaticCall) {
            return;
        }

        // no parent class
        if (! is_string($parentClassName)) {
            return;
        }

        if (! $node->class instanceof Name) {
            return;
        }

        // rename ParentClass
        if ($this->isName($node->class, $parentClassName)) {
            $node->class = new Name('parent');
        }

        if (! $this->isName($node->class, 'parent')) {
            return;
        }

        // it's not a parent PHP 4 constructor call
        if (! $this->isNameInsensitive($node, $parentClassName)) {
            return;
        }

        $node->name = new Identifier('__construct');
    }
}
