<?php declare(strict_types=1);

namespace Rector\Symfony\Rector\New_;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Scalar\String_;
use Rector\Node\NodeFactory;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see https://github.com/symfony/symfony/pull/27821/files
 */
final class StringToArrayArgumentProcessRector extends AbstractRector
{
    /**
     * @var string
     */
    private $processClass;

    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    /**
     * @var string
     */
    private $processHelperClass;

    public function __construct(
        NodeFactory $nodeFactory,
        string $processClass = 'Symfony\Component\Process\Process',
        string $processHelperClass = 'Symfony\Component\Console\Helper\ProcessHelper'
    ) {
        $this->nodeFactory = $nodeFactory;
        $this->processClass = $processClass;
        $this->processHelperClass = $processHelperClass;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Changes Process string argument to an array', [
            new CodeSample(
                <<<'CODE_SAMPLE'
use Symfony\Component\Process\Process;
$process = new Process('ls -l');
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
use Symfony\Component\Process\Process;
$process = new Process(['ls', '-l']);
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [New_::class, MethodCall::class];
    }

    /**
     * @param New_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->isType($node, $this->processClass)) {
            return $this->processArgumentPosition($node, 0);
        }

        if ($this->isType($node, $this->processHelperClass)) {
            return $this->processArgumentPosition($node, 1);
        }

        return null;
    }

    /**
     * @param New_|MethodCall $node
     */
    private function processArgumentPosition(Node $node, int $argumentPosition): ?Node
    {
        if (! isset($node->args[$argumentPosition])) {
            return null;
        }

        $firstArgument = $node->args[$argumentPosition]->value;
        if ($firstArgument instanceof Array_) {
            return null;
        }

        if (! $firstArgument instanceof String_) {
            return null;
        }

        $parts = Strings::split($firstArgument->value, '# #');
        $node->args[$argumentPosition]->value = $this->nodeFactory->createArray(...$parts);

        return $node;
    }
}
