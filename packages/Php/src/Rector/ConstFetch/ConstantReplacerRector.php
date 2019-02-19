<?php declare(strict_types=1);

namespace Rector\Php\Rector\ConstFetch;

use PhpParser\Node;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Name;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class ConstantReplacerRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private $oldToNewConstants = [];

    /**
     * @param string[] $oldToNewConstants
     */
    public function __construct(array $oldToNewConstants = [])
    {
        $this->oldToNewConstants = $oldToNewConstants;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Replace constant by new ones', [
            new CodeSample(
                <<<'CODE_SAMPLE'
final class SomeClass
{
    public function run()
    {
        return MYSQL_ASSOC;
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
final class SomeClass
{
    public function run()
    {
        return MYSQLI_ASSOC;
    }
}
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [ConstFetch::class];
    }

    /**
     * @param ConstFetch $node
     */
    public function refactor(Node $node): ?Node
    {
        foreach ($this->oldToNewConstants as $oldConstant => $newConstant) {
            if (! $this->isNameInsensitive($node, $oldConstant)) {
                continue;
            }

            $node->name = new Name($newConstant);
            break;
        }

        return $node;
    }
}
