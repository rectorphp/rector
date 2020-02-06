<?php

declare(strict_types=1);

namespace Rector\Core\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\Core\Tests\Rector\MethodCall\MethodCallToAnotherMethodCallWithArgumentsRector\MethodCallToAnotherMethodCallWithArgumentsRectorTest
 */
final class MethodCallToAnotherMethodCallWithArgumentsRector extends AbstractRector
{
    /**
     * @var mixed[][][]
     */
    private $oldMethodsToNewMethodsWithArgsByType = [];

    /**
     * @param mixed[][][] $oldMethodsToNewMethodsWithArgsByType
     */
    public function __construct(array $oldMethodsToNewMethodsWithArgsByType = [])
    {
        $this->oldMethodsToNewMethodsWithArgsByType = $oldMethodsToNewMethodsWithArgsByType;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Turns old method call with specific types to new one with arguments', [
            new ConfiguredCodeSample(
                <<<'PHP'
$serviceDefinition = new Nette\DI\ServiceDefinition;
$serviceDefinition->setInject();
PHP
                ,
                <<<'PHP'
$serviceDefinition = new Nette\DI\ServiceDefinition;
$serviceDefinition->addTag('inject');
PHP
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
            if (! $this->isObjectType($node, $type)) {
                continue;
            }

            foreach ($oldMethodsToNewMethodsWithArgs as $oldMethod => $newMethodsWithArgs) {
                if (! $this->isName($node->name, $oldMethod)) {
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
