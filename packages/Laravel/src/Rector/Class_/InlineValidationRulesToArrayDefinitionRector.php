<?php declare(strict_types=1);

namespace Rector\Laravel\Rector\Class_;

use LogicException;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Return_;
use Rector\Exception\ShouldNotHappenException;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\Laravel\Tests\Rector\Class_\InlineValidationRulesToArrayDefinitionRector\InlineValidationRulesToArrayDefinitionRectorTest
 */
final class InlineValidationRulesToArrayDefinitionRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Transforms inline validation rules to array definition', [
            new CodeSample(
                <<<'CODE_SAMPLE'
use Illuminate\Foundation\Http\FormRequest;

class SomeClass extends FormRequest
{
    public function rules(): array
    {
        return [
            'someAttribute' => 'required|string|exists:' . SomeModel::class . 'id',
        ];
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
use Illuminate\Foundation\Http\FormRequest;

class SomeClass extends FormRequest
{
    public function rules(): array
    {
        return [
            'someAttribute' => ['required', 'string', \Illuminate\Validation\Rule::exists(SomeModel::class, 'id')],
        ];
    }
}
CODE_SAMPLE
            ),
        ]);
    }

    public function getNodeTypes(): array
    {
        return [Class_::class];
    }

    /**
     * @param Node\Stmt\Class_ $node
     * @return Node|null
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->isObjectType($node, 'Illuminate\Foundation\Http\FormRequest') === false) {
            return null;
        }

        $rules = $node->getMethod('rules');
        if ($rules === null || $rules->stmts === null) {
            return null;
        }

        foreach ($rules->stmts as $statement) {
            if (! $statement instanceof Return_) {
                continue;
            }

            $expression = $statement->expr;
            if (! $expression instanceof Array_) {
                continue;
            }

            foreach ($expression->items as $item) {
                if (! $item->value instanceof String_ && ! $item->value instanceof Concat) {
                    continue;
                }

                $newRules = $this->transformRulesSetToExpressionsArray($item->value);
                foreach ($newRules as &$newRule) {
                    if (! ($newRule instanceof Concat)) {
                        continue;
                    }

                    try {
                        $fullString = $this->transformConcatExpressionToSingleString($newRule);
                    } catch (LogicException $logicException) {
                        continue;
                    }

                    $matchesExist = preg_match('#^exists:(\w+),(\w+)$#', $fullString, $matches);
                    if ($matchesExist === false || $matchesExist === 0) {
                        continue;
                    }

                    $ruleClass = $matches[1];
                    $ruleAttribute = $matches[2];

                    $newRule = new StaticCall(
                        new Name('\Illuminate\Validation\Rule'),
                        'exists',
                        [
                            new Arg(new ClassConstFetch(new Name($ruleClass), 'class')),
                            new Arg(new String_($ruleAttribute)),
                        ]
                    );
                }

                $item->value = $this->createArray($newRules);
            }
        }

        return $node;
    }

    /**
     * @return Node\Expr[]
     */
    private function transformRulesSetToExpressionsArray(Expr $expr): array
    {
        if ($expr instanceof String_) {
            return array_map(static function (string $value): String_ {
                return new String_($value);
            }, explode('|', $expr->value));
        } elseif ($expr instanceof Concat) {
            $left = $this->transformRulesSetToExpressionsArray($expr->left);
            $expr->left = $left[count($left) - 1];

            $right = $this->transformRulesSetToExpressionsArray($expr->right);
            $expr->right = $right[0];

            return array_merge(array_slice($left, 0, -1), [$expr], array_slice($right, 1));
        } elseif ($expr instanceof ClassConstFetch
            || $expr instanceof MethodCall
            || $expr instanceof FuncCall
        ) {
            return [$expr];
        }

        throw new ShouldNotHappenException('Unexpected call ' . get_class($expr));
    }

    private function transformConcatExpressionToSingleString(Concat $concat): string
    {
        $output = '';

        foreach ([$concat->left, $concat->right] as $expressionPart) {
            if ($expressionPart instanceof String_) {
                $output .= $expressionPart->value;
            } elseif ($expressionPart instanceof Concat) {
                $output .= $this->transformConcatExpressionToSingleString($expressionPart);
            } elseif ($expressionPart instanceof ClassConstFetch) {
                /** @var Node\Name $name */
                $name = $expressionPart->class->getAttribute('originalName');

                $output .= implode('\\', $name->parts);
            } else {
                throw new ShouldNotHappenException('Unexpected expression type ' . get_class($expressionPart));
            }
        }

        return $output;
    }
}
