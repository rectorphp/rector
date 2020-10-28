<?php

declare(strict_types=1);

namespace Rector\Phalcon\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see https://github.com/rectorphp/rector/issues/2571
 *
 * @see \Rector\Phalcon\Tests\Rector\MethodCall\DecoupleSaveMethodCallWithArgumentToAssignRector\DecoupleSaveMethodCallWithArgumentToAssignRectorTest
 */
final class DecoupleSaveMethodCallWithArgumentToAssignRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Decouple Phalcon\Mvc\Model::save() with argument to assign()', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run(\Phalcon\Mvc\Model $model, $data)
    {
        $model->save($data);
    }
}
CODE_SAMPLE
,
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run(\Phalcon\Mvc\Model $model, $data)
    {
        $model->save();
        $model->assign($data);
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
        return [MethodCall::class];
    }

    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isObjectType($node->var, 'Phalcon\Mvc\Model')) {
            return null;
        }

        if (! $this->isName($node->name, 'save')) {
            return null;
        }

        if ($node->args === []) {
            return null;
        }

        $assignMethodCall = $this->createMethodCall($node->var, 'assign');
        $assignMethodCall->args = $node->args;
        $node->args = [];

        $this->addNodeAfterNode($assignMethodCall, $node);

        return $node;
    }
}
