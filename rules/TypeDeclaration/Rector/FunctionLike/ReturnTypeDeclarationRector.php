<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\FunctionLike;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use Rector\Core\Contract\Rector\DeprecatedRectorInterface;
use Rector\Core\Rector\AbstractRector;
use RectorPrefix202212\Symfony\Component\Console\Style\SymfonyStyle;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/scalar_type_hints_v5
 *
 * @deprecated Moving doc types to type declarations is dangerous. Use specific strict types instead.
 * This rule will be split info many small ones.
 */
final class ReturnTypeDeclarationRector extends AbstractRector implements DeprecatedRectorInterface
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
        return [Function_::class, ClassMethod::class];
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change @return types and type from static analysis to type declarations if not a BC-break', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    /**
     * @return int
     */
    public function getCount()
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function getCount(): int
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
        $this->symfonyStyle->error('This rule was split info many small ones as breaking types based on falsy docblocks. Use specific rules to infer return instead.');
        \sleep(5);
        return null;
    }
}
