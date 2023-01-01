<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\FunctionLike;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use Rector\Core\Contract\Rector\DeprecatedRectorInterface;
use Rector\Core\Rector\AbstractRector;
use RectorPrefix202301\Symfony\Component\Console\Style\SymfonyStyle;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @deprecated Moving doc types to type declarations is dangerous. Use specific strict type inferers instead.
 * Use specific rules to infer params instead. This rule will be split into many small ones.
 */
final class ParamTypeDeclarationRector extends AbstractRector implements DeprecatedRectorInterface
{
    /**
     * @readonly
     * @var \Symfony\Component\Console\Style\SymfonyStyle
     */
    private $symfonyStyle;
    public function __construct(SymfonyStyle $symfonyStyle)
    {
        $this->symfonyStyle = $symfonyStyle;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        // why not on Param node? because class like docblock is edited too for @param tags
        return [Function_::class, ClassMethod::class];
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change @param types to type declarations if not a BC-break', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    /**
     * @param int $number
     */
    public function run($number)
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    public function run(int $number)
    {
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @param ClassMethod|Function_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        $this->symfonyStyle->error('Use specific rules to infer params instead. This rule was split into many small ones.');
        \sleep(5);
        return null;
    }
}
