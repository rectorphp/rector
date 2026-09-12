<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use Rector\Rector\AbstractRector;
use Rector\TypeDeclaration\NodeAnalyzer\ArrayCallbackParamTypeResolver;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\FuncCall\AddArrayAnyAllClosureParamTypeRector\AddArrayAnyAllClosureParamTypeRectorTest
 */
final class AddArrayAnyAllClosureParamTypeRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     */
    private ArrayCallbackParamTypeResolver $arrayCallbackParamTypeResolver;
    /**
     * @var string[]
     */
    private const FUNCTION_NAMES = ['array_any', 'array_all'];
    public function __construct(ArrayCallbackParamTypeResolver $arrayCallbackParamTypeResolver)
    {
        $this->arrayCallbackParamTypeResolver = $arrayCallbackParamTypeResolver;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add a type to an untyped array_any()/array_all() closure param, based on the array item type', [new CodeSample(<<<'CODE_SAMPLE'
/** @var string[] $items */
array_any($items, fn ($item): bool => $item !== '');
CODE_SAMPLE
, <<<'CODE_SAMPLE'
/** @var string[] $items */
array_any($items, fn (string $item): bool => $item !== '');
CODE_SAMPLE
)]);
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
        return $this->arrayCallbackParamTypeResolver->refactorFirstParamType($node, self::FUNCTION_NAMES, \false);
    }
    public function provideMinPhpVersion(): int
    {
        return PhpVersionFeature::ARRAY_ANY;
    }
}
