<?php declare(strict_types=1);

namespace Rector\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\ConfiguredCodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class MethodCallToAnotherMethodCallWithArgumentsRector extends AbstractRector
{
    /**
     * @var mixed[][][]
     */
    private $oldMethodsToNewMethodsWithArgsByType = [];

    /**
     * @param mixed[][][] $oldMethodsToNewMethodsWithArgsByType
     */
    public function __construct(array $oldMethodsToNewMethodsWithArgsByType)
    {
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
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        foreach ($this->oldMethodsToNewMethodsWithArgsByType as $type => $oldMethodsToNewMethodsWithArgs) {
            if (! $this->isType($node, $type)) {
                continue;
            }

            foreach ($oldMethodsToNewMethodsWithArgs as $oldMethod => $newMethodsWithArgs) {
                if (! $this->isName($node, $oldMethod)) {
                    continue;
                }

                $node->name = new Identifier($newMethodsWithArgs[0]);
                $node->args = $this->createArgs($newMethodsWithArgs[1]);

                return $node;
            }
        }

        return $node;
    }
}
