<?php declare(strict_types=1);

namespace Rector\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use Rector\Builder\IdentifierRenamer;
use Rector\Node\NodeFactory;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\ConfiguredCodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class MethodCallToAnotherMethodCallWithArgumentsRector extends AbstractRector
{
    /**
     * @var IdentifierRenamer
     */
    private $identifierRenamer;

    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    /**
     * @var mixed[][][]
     */
    private $oldMethodsToNewMethodsWithArgsByType = [];

    /**
     * @param mixed[][][] $oldMethodsToNewMethodsWithArgsByType
     */
    public function __construct(
        IdentifierRenamer $identifierRenamer,
        NodeFactory $nodeFactory,
        array $oldMethodsToNewMethodsWithArgsByType
    ) {
        $this->identifierRenamer = $identifierRenamer;
        $this->nodeFactory = $nodeFactory;
        $this->oldMethodsToNewMethodsWithArgsByType = $oldMethodsToNewMethodsWithArgsByType;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Turns old method call with specfici type to new one with arguments', [
            new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
$serviceDefinition = new Nette\DI\ServiceDefinition;
$serviceDefinition->setInject();
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
$serviceDefinition = new Nette\DI\ServiceDefinition;
$serviceDefinition->addTag('inject');
CODE_SAMPLE
                ,
                [
                    'Nette\DI\ServiceDefinition' => [
                        'setInject' => [['addTag', ['inject']]],
                    ],
                ]
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class];
    }

    /**
     * @param MethodCall $methodCallNode
     */
    public function refactor(Node $methodCallNode): ?Node
    {
        foreach ($this->oldMethodsToNewMethodsWithArgsByType as $type => $oldMethodsToNewMethodsWithArgs) {
            // @todo is name should be possibly part of AbstractRector
            if (! $this->isType($methodCallNode, $type)) {
                continue;
            }

            foreach ($oldMethodsToNewMethodsWithArgs as $oldMethod => $newMethodsWithArgs) {
                if (! $this->isName($methodCallNode, $oldMethod)) {
                    continue;
                }

                $this->identifierRenamer->renameNode($methodCallNode, $newMethodsWithArgs[0]);
                $methodCallNode->args = $this->nodeFactory->createArgs($newMethodsWithArgs[1]);

                return $methodCallNode;
            }
        }

        return $methodCallNode;
    }
}
