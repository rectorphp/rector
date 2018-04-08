<?php declare(strict_types=1);

namespace Rector\RectorBuilder;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use Rector\Node\NodeFactory;
use Rector\NodeAnalyzer\MethodCallAnalyzer;
use Rector\NodeChanger\IdentifierRenamer;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class BuilderRector extends AbstractRector
{
    /**
     * @var MethodCallAnalyzer
     */
    private $methodCallAnalyzer;

    /**
     * @var string|null
     */
    private $methodCallType;

    /**
     * @var string|null
     */
    private $methodName;

    /**
     * @var string|null
     */
    private $newMethodName;

    /**
     * @var mixed[]
     */
    private $newArguments = [];

    /**
     * @var IdentifierRenamer
     */
    private $identifierRenamer;

    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    public function __construct(
        MethodCallAnalyzer $methodCallAnalyzer,
        IdentifierRenamer $identifierRenamer,
        NodeFactory $nodeFactory
    ) {
        $this->methodCallAnalyzer = $methodCallAnalyzer;
        $this->identifierRenamer = $identifierRenamer;
        $this->nodeFactory = $nodeFactory;
    }

    public function matchMethodCallByType(string $methodCallType): self
    {
        $this->methodCallType = $methodCallType;
        return $this;
    }

    public function matchMethodName(string $methodName): self
    {
        $this->methodName = $methodName;
        return $this;
    }

    public function changeMethodNameTo(string $newMethodName): self
    {
        $this->newMethodName = $newMethodName;
        return $this;
    }

    /**
     * @param mixed $value
     */
    public function addArgument(int $position, $value): self
    {
        $this->newArguments[$position] = $value;
        return $this;
    }

    public function isCandidate(Node $node): bool
    {
        if ($this->methodCallType) {
            if (! $this->methodCallAnalyzer->isType($node, $this->methodCallType)) {
                return false;
            }
        }

        if ($this->methodName) {
            if (! $this->methodCallAnalyzer->isMethod($node, $this->methodName)) {
                return false;
            }
        }

        return true;
    }

    public function refactor(Node $node): ?Node
    {
        if ($this->newMethodName && $node instanceof MethodCall) {
            $this->identifierRenamer->renameNode($node, $this->newMethodName);
        }

        if ($this->newArguments && $node instanceof MethodCall) {
            foreach ($this->newArguments as $position => $argument) {
                // to check adding arguments in order
                if (! isset($node->args[$position]) && isset($node->args[$position - 1])) {
                    $node->args[$position] = $this->nodeFactory->createArg($argument);
                }
            }
        }

        return $node;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'This can handle class renames, method renames and argument changes, based on custom configuration.',
            [
                new CodeSample('<?php new OldClass;', '<?php new NewClass;'),
            ]
        );
    }
}
