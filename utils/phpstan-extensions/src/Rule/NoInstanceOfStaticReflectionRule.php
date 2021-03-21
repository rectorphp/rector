<?php

declare(strict_types=1);

namespace Rector\PHPStanExtensions\Rule;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\Type;
use Rector\PHPStanExtensions\TypeAnalyzer\AllowedAutoloadedTypeAnalyzer;
use Symplify\Astral\Naming\SimpleNameResolver;
use Symplify\PHPStanRules\Rules\AbstractSymplifyRule;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see https://github.com/rectorphp/rector/issues/5906
 *
 * @see \Rector\PHPStanExtensions\Tests\Rule\NoInstanceOfStaticReflectionRule\NoInstanceOfStaticReflectionRuleTest
 */
final class NoInstanceOfStaticReflectionRule extends AbstractSymplifyRule implements Rule
{
    /**
     * @var string
     */
    public const ERROR_MESSAGE = 'Instead of "instanceof/is_a()" use ReflectionProvider service or "(new ObjectType(<desired_type>))->isSuperTypeOf(<element_type>)" for static reflection to work';

    /**
     * @var AllowedAutoloadedTypeAnalyzer
     */
    private $allowedAutoloadedTypeAnalyzer;

    /**
     * @var SimpleNameResolver
     */
    private $simpleNameResolver;

    public function __construct(
        SimpleNameResolver $simpleNameResolver,
        AllowedAutoloadedTypeAnalyzer $allowedAutoloadedTypeAnalyzer
    ) {
        $this->allowedAutoloadedTypeAnalyzer = $allowedAutoloadedTypeAnalyzer;
        $this->simpleNameResolver = $simpleNameResolver;
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Instanceof_::class, FuncCall::class];
    }

    /**
     * @param Instanceof_|FuncCall $node
     * @return string[]
     */
    public function process(Node $node, Scope $scope): array
    {
        $exprStaticType = $this->resolveExprStaticType($node, $scope);
        if ($exprStaticType === null) {
            return [];
        }

        if ($this->allowedAutoloadedTypeAnalyzer->isAllowedType($exprStaticType)) {
            return [];
        }

        return [self::ERROR_MESSAGE];
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(self::ERROR_MESSAGE, [
            new CodeSample(
                <<<'CODE_SAMPLE'
return is_a($node, 'Command', true);
CODE_SAMPLE
            ,
                <<<'CODE_SAMPLE'
$nodeType = $scope->getType($node);
$commandObjectType = new ObjectType('Command');

return $commandObjectType->isSuperTypeOf($nodeType)->yes();
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @param FuncCall|Instanceof_ $node
     */
    private function resolveExprStaticType(Node $node, Scope $scope): ?Type
    {
        if ($node instanceof Instanceof_) {
            if ($node->class instanceof Name) {
                return new ConstantStringType($node->class->toString());
            }

            return $scope->getType($node->class);
        }

        if (! $this->simpleNameResolver->isName($node, 'is_a')) {
            return null;
        }

        $typeArgValue = $node->args[1]->value;
        return $scope->getType($typeArgValue);
    }
}
