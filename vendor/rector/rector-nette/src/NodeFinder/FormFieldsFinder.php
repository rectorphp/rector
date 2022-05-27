<?php

declare (strict_types=1);
namespace Rector\Nette\NodeFinder;

use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Expression;
use PHPStan\Type\ObjectType;
use Rector\Nette\ValueObject\FormField;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\NodeTypeResolver;
/**
 * @see \Rector\Nette\Tests\NodeFinder\FormFinder\FormFinderTest
 */
final class FormFieldsFinder
{
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(\Rector\NodeTypeResolver\NodeTypeResolver $nodeTypeResolver, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->nodeNameResolver = $nodeNameResolver;
    }
    /**
     * @return FormField[]
     */
    public function find(\PhpParser\Node\Stmt\Class_ $class, \PhpParser\Node\Expr\Variable $form) : array
    {
        $formFields = [];
        foreach ($class->getMethods() as $classMethod) {
            $stmts = $classMethod->getStmts();
            if ($stmts === null) {
                continue;
            }
            foreach ($stmts as $stmt) {
                if (!$stmt instanceof \PhpParser\Node\Stmt\Expression) {
                    continue;
                }
                $methodCall = $this->findMethodCall($stmt);
                if (!$methodCall instanceof \PhpParser\Node\Expr\MethodCall) {
                    continue;
                }
                $addFieldMethodCall = $this->findAddFieldMethodCall($methodCall);
                if (!$addFieldMethodCall instanceof \PhpParser\Node\Expr\MethodCall) {
                    continue;
                }
                if (!$this->isFormAddFieldMethodCall($addFieldMethodCall, $form)) {
                    continue;
                }
                $formFields = $this->addFormField($formFields, $addFieldMethodCall, $methodCall);
            }
        }
        return $formFields;
    }
    private function findMethodCall(\PhpParser\Node\Stmt\Expression $expression) : ?\PhpParser\Node\Expr\MethodCall
    {
        $methodCall = null;
        if ($expression->expr instanceof \PhpParser\Node\Expr\MethodCall) {
            $methodCall = $expression->expr;
        } elseif ($expression->expr instanceof \PhpParser\Node\Expr\Assign && $expression->expr->expr instanceof \PhpParser\Node\Expr\MethodCall) {
            $methodCall = $expression->expr->expr;
        }
        return $methodCall;
    }
    private function findAddFieldMethodCall(\PhpParser\Node\Expr\MethodCall $methodCall) : ?\PhpParser\Node\Expr\MethodCall
    {
        if ($methodCall->var instanceof \PhpParser\Node\Expr\Variable) {
            // skip submit buttons
            if ($this->nodeTypeResolver->isObjectType($methodCall, new \PHPStan\Type\ObjectType('Nette\\Forms\\Controls\\SubmitButton'))) {
                return null;
            }
            if ($this->nodeTypeResolver->isObjectType($methodCall, new \PHPStan\Type\ObjectType('Nette\\Forms\\Container'))) {
                return $methodCall;
            }
            // skip groups, renderers, translator etc.
            if ($this->nodeTypeResolver->isObjectType($methodCall, new \PHPStan\Type\ObjectType('Nette\\Forms\\Controls\\BaseControl'))) {
                return $methodCall;
            }
            return null;
        }
        if ($methodCall->var instanceof \PhpParser\Node\Expr\MethodCall) {
            return $this->findAddFieldMethodCall($methodCall->var);
        }
        return null;
    }
    private function isFormAddFieldMethodCall(\PhpParser\Node\Expr\MethodCall $addFieldMethodCall, \PhpParser\Node\Expr\Variable $form) : bool
    {
        $methodCallVariable = $this->findMethodCallVariable($addFieldMethodCall);
        if (!$methodCallVariable instanceof \PhpParser\Node\Expr\Variable) {
            return \false;
        }
        return $methodCallVariable->name === $form->name;
    }
    private function findMethodCallVariable(\PhpParser\Node\Expr\MethodCall $methodCall) : ?\PhpParser\Node\Expr\Variable
    {
        if ($methodCall->var instanceof \PhpParser\Node\Expr\Variable) {
            return $methodCall->var;
        }
        if ($methodCall->var instanceof \PhpParser\Node\Expr\MethodCall) {
            return $this->findMethodCallVariable($methodCall->var);
        }
        return null;
    }
    /**
     * @param FormField[] $formFields
     * @return FormField[]
     */
    private function addFormField(array $formFields, \PhpParser\Node\Expr\MethodCall $addFieldMethodCall, \PhpParser\Node\Expr\MethodCall $methodCall) : array
    {
        $arg = $addFieldMethodCall->args[0] ?? null;
        if (!$arg) {
            return $formFields;
        }
        $name = $arg->value;
        if (!$name instanceof \PhpParser\Node\Scalar\String_) {
            return $formFields;
        }
        $formFields[] = new \Rector\Nette\ValueObject\FormField($name->value, $this->resolveFieldType($this->nodeNameResolver->getName($addFieldMethodCall->name)), $this->isFieldRequired($methodCall));
        return $formFields;
    }
    private function isFieldRequired(\PhpParser\Node\Expr\MethodCall $methodCall) : bool
    {
        if ($methodCall->name instanceof \PhpParser\Node\Identifier && $methodCall->name->name === 'setRequired') {
            // TODO addRule(Form:FILLED) is also required
            return \true;
        }
        if ($methodCall->var instanceof \PhpParser\Node\Expr\MethodCall) {
            return $this->isFieldRequired($methodCall->var);
        }
        return \false;
    }
    private function resolveFieldType(?string $methodName) : string
    {
        switch ($methodName) {
            case 'addInteger':
                return 'int';
            case 'addContainer':
                return 'array';
            case 'addCheckbox':
                return 'bool';
            default:
                return 'string';
        }
    }
}
