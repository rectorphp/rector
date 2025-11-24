<?php

declare (strict_types=1);
namespace Rector\Symfony\Symfony73\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\ArrayItem;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\Rector\AbstractRector;
use Rector\Symfony\Enum\SymfonyClass;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Symfony\Tests\Symfony73\Rector\Class_\ConstraintOptionsToNamedArgumentsRector\ConstraintOptionsToNamedArgumentsRectorTest
 */
final class ConstraintOptionsToNamedArgumentsRector extends AbstractRector
{
    /**
     * @readonly
     */
    private ValueResolver $valueResolver;
    public function __construct(ValueResolver $valueResolver)
    {
        $this->valueResolver = $valueResolver;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Refactor Symfony constraints using array options to named arguments syntax for better readability and type safety.', [new CodeSample(<<<'CODE_SAMPLE'
use Symfony\Component\Validator\Constraints\NotBlank;

$constraint = new NotBlank(['message' => 'This field should not be blank.']);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Component\Validator\Constraints\NotBlank;

$constraint = new NotBlank(message: 'This field should not be blank.');
CODE_SAMPLE
)]);
    }
    public function getNodeTypes(): array
    {
        return [New_::class];
    }
    public function refactor(Node $node): ?Node
    {
        if (!$node instanceof New_) {
            return null;
        }
        if ($node->isFirstClassCallable()) {
            return null;
        }
        // Match classes starting with Symfony\Component\Validator\Constraints\
        if (!$node->class instanceof FullyQualified && !$node->class instanceof Name) {
            return null;
        }
        $className = $this->getName($node->class);
        if (!is_string($className)) {
            return null;
        }
        if (strncmp($className, 'Symfony\Component\Validator\Constraints\\', strlen('Symfony\Component\Validator\Constraints\\')) !== 0 && strncmp($className, 'Symfony\Bridge\Doctrine\Validator\Constraints\\', strlen('Symfony\Bridge\Doctrine\Validator\Constraints\\')) !== 0) {
            return null;
        }
        if ($node->args === [] || !$node->args[0] instanceof Arg || !$node->args[0]->value instanceof Array_) {
            return null;
        }
        $argName = $node->args[0]->name;
        if ($argName instanceof Identifier && $argName->name !== 'options') {
            return null;
        }
        $args = $node->getArgs();
        if ($className === SymfonyClass::SYMFONY_VALIDATOR_CONSTRAINTS_COLLECTION && count($args) === 1 && $args[0]->value instanceof Array_) {
            if ($args[0]->name instanceof Identifier) {
                return null;
            }
            $args[0]->name = new Identifier('fields');
            return $node;
        }
        $array = $node->args[0]->value;
        $namedArgs = [];
        foreach ($array->items as $item) {
            if (!$item instanceof ArrayItem) {
                continue;
            }
            if (!$item->key instanceof Expr) {
                // handle nested array
                if ($item->value instanceof New_) {
                    return null;
                }
                continue;
            }
            $keyValue = $this->valueResolver->getValue($item->key);
            if (!is_string($keyValue)) {
                continue;
            }
            $arg = new Arg($item->value);
            $arg->name = new Identifier($keyValue);
            $namedArgs[] = $arg;
        }
        if ($namedArgs === []) {
            return null;
        }
        $node->args = $namedArgs;
        return $node;
    }
}
