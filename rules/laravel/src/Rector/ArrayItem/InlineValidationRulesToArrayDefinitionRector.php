<?php

declare(strict_types=1);

namespace Rector\Laravel\Rector\ArrayItem;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see \Rector\Laravel\Tests\Rector\ArrayItem\InlineValidationRulesToArrayDefinitionRector\InlineValidationRulesToArrayDefinitionRectorTest
 */
final class InlineValidationRulesToArrayDefinitionRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Transforms inline validation rules to array definition', [
            new CodeSample(
                <<<'PHP'
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
PHP
                ,
                <<<'PHP'
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
PHP
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [ArrayItem::class];
    }

    /**
     * @param ArrayItem $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->shouldSkipArrayItem($node)) {
            return null;
        }

        $newRules = $this->createNewRules($node);
        $node->value = $this->createArray($newRules);

        return $node;
    }

    private function shouldSkipArrayItem(ArrayItem $arrayItem): bool
    {
        $classLike = $arrayItem->getAttribute(AttributeKey::CLASS_NODE);
        if ($classLike === null) {
            return true;
        }

        if (! $this->isObjectType($classLike, 'Illuminate\Foundation\Http\FormRequest')) {
            return true;
        }

        $classMethod = $arrayItem->getAttribute(AttributeKey::METHOD_NODE);
        if ($classMethod === null) {
            return true;
        }

        if (! $this->isName($classMethod, 'rules')) {
            return true;
        }
        return ! $arrayItem->value instanceof String_ && ! $arrayItem->value instanceof Concat;
    }

    /**
     * @return Expr[]
     */
    private function createNewRules(ArrayItem $arrayItem): array
    {
        $newRules = $this->transformRulesSetToExpressionsArray($arrayItem->value);

        foreach ($newRules as $key => $newRule) {
            if (! $newRule instanceof Concat) {
                continue;
            }

            $fullString = $this->transformConcatExpressionToSingleString($newRule);
            if ($fullString === null) {
                return [];
            }

            $matches = Strings::match($fullString, '#^exists:(?<ruleClass>\w+),(?<ruleAttribute>\w+)$#');
            if ($matches === null) {
                continue;
            }

            $ruleClass = $matches['ruleClass'];
            $ruleAttribute = $matches['ruleAttribute'];

            $arguments = [new ClassConstFetch(new Name($ruleClass), 'class'), new String_($ruleAttribute)];

            $ruleExistsStaticCall = $this->createStaticCall('Illuminate\Validation\Rule', 'exists', $arguments);
            $newRules[$key] = $ruleExistsStaticCall;
        }

        return $newRules;
    }

    /**
     * @return \PhpParser\Node\Scalar\String_[]|\PhpParser\Node\Expr[]|\PhpParser\Node\Expr\ClassConstFetch[]|FuncCall[]|MethodCall[]
     */
    private function transformRulesSetToExpressionsArray(Expr $expr): array
    {
        if ($expr instanceof String_) {
            return array_map(static function (string $value): String_ {
                return new String_($value);
            }, explode('|', $expr->value));
        }

        if ($expr instanceof Concat) {
            $left = $this->transformRulesSetToExpressionsArray($expr->left);
            $expr->left = $left[count($left) - 1];

            $right = $this->transformRulesSetToExpressionsArray($expr->right);
            $expr->right = $right[0];

            return array_merge(array_slice($left, 0, -1), [$expr], array_slice($right, 1));
        }

        if ($expr instanceof ClassConstFetch || $expr instanceof MethodCall || $expr instanceof FuncCall) {
            return [$expr];
        }

        throw new ShouldNotHappenException('Unexpected call ' . get_class($expr));
    }

    private function transformConcatExpressionToSingleString(Concat $concat): ?string
    {
        $output = '';

        foreach ([$concat->left, $concat->right] as $expressionPart) {
            if ($expressionPart instanceof String_) {
                $output .= $expressionPart->value;
            } elseif ($expressionPart instanceof Concat) {
                $output .= $this->transformConcatExpressionToSingleString($expressionPart);
            } elseif ($expressionPart instanceof ClassConstFetch) {
                /** @var Name $name */
                $name = $expressionPart->class->getAttribute(AttributeKey::ORIGINAL_NAME);

                $output .= implode('\\', $name->parts);
            } else {
                // unable to process
                return null;
            }
        }

        return $output;
    }
}
