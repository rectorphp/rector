<?php

declare(strict_types=1);

namespace Rector\DowngradePhp72\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\Cast\Bool_;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PHPStan\Type\BooleanType;
use Rector\Core\NodeAnalyzer\ArgsAnalyzer;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Tests\DowngradePhp72\Rector\FuncCall\DowngradeJsonDecodeNullAssociativeArgRector\DowngradeJsonDecodeNullAssociativeArgRectorTest
 */
final class DowngradeJsonDecodeNullAssociativeArgRector extends AbstractRector
{
    public function __construct(
        private readonly ArgsAnalyzer $argsAnalyzer
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Downgrade json_decode() with null associative argument function', [
            new CodeSample(
                <<<'CODE_SAMPLE'
declare(strict_types=1);

function exactlyNull(string $json)
{
    $value = json_decode($json, null);
}

function possiblyNull(string $json, ?bool $associative)
{
    $value = json_decode($json, $associative);
}
CODE_SAMPLE

                ,
                <<<'CODE_SAMPLE'
declare(strict_types=1);

function exactlyNull(string $json)
{
    $value = json_decode($json, false);
}

function possiblyNull(string $json, ?bool $associative)
{
    $value = json_decode($json, (bool) $associative);
}
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [FuncCall::class];
    }

    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isName($node, 'json_decode')) {
            return null;
        }

        $createdByRule = $node->getAttribute(AttributeKey::CREATED_BY_RULE) ?? [];
        if (in_array(self::class, $createdByRule, true)) {
            return null;
        }

        $args = $node->getArgs();
        if ($this->argsAnalyzer->hasNamedArg($args)) {
            return null;
        }

        if (! isset($args[1])) {
            return null;
        }

        $associativeValue = $args[1]->value;

        if ($associativeValue instanceof Bool_) {
            return null;
        }

        $type = $this->nodeTypeResolver->getType($associativeValue);

        if ($type instanceof BooleanType) {
            return null;
        }

        if ($associativeValue instanceof ConstFetch && $this->valueResolver->isNull($associativeValue)) {
            $node->args[1]->value = $this->nodeFactory->createFalse();
            return $node;
        }

        $node->args[1]->value = new Bool_($associativeValue);
        return $node;
    }
}
