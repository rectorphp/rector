<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use PHPStan\Type\Type;
use Rector\Core\Contract\Rector\DeprecatedRectorInterface;
use Rector\Core\Rector\AbstractRector;
use RectorPrefix202212\Symfony\Component\Console\Style\SymfonyStyle;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @deprecated This rule move doctypes to strict type declarations and often breaks the code.
 * Instead use specific rules that handle exact type declarations.
 */
final class PropertyTypeDeclarationRector extends AbstractRector implements DeprecatedRectorInterface
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
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add @var to properties that are missing it', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    private $value;

    public function run()
    {
        $this->value = 123;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    /**
     * @var int
     */
    private $value;

    public function run()
    {
        $this->value = 123;
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Property::class];
    }
    /**
     * @param Property $node
     */
    public function refactor(Node $node) : ?Node
    {
        $this->symfonyStyle->error('The PropertyTypeDeclaration rule is deprecated, as it works with doc block types that are not reliable and adds invalid  types');
        \sleep(5);
        return null;
    }
}
