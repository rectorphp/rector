<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Rector\ClassMethod;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Throw_;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class AnnotateThrowables extends AbstractRector
{
    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        // what node types we look for?
        // pick any node from https://github.com/rectorphp/rector/blob/master/docs/NodesOverview.md
        return [Throw_::class];
    }

    /**
     * @param Throw_ $node - we can add "MethodCall" type here, because only this node is in "getNodeTypes()"
     *
     * @return Node|null
     */
    public function refactor(Node $node): ?Node
    {
        /*
        // Try to get the method in which this throw statement is
        $method = $node->getAttribute('methodNode');
        dump($this->isThrowAnnotated($method, $node->expr->class->parts));
        dd('fine');

        /*
        // we only care about "set*" method names
        if (! $this->isName($node->name, 'set*')) {
            // return null to skip it
            return null;
        }

        $methodCallName = $this->getName($node);
        $newMethodCallName = Strings::replace($methodCallName, '#^set#', 'change');

        $node->name = new Identifier($newMethodCallName);

        // return $node if you modified it
        return $node;
        */

        return $node;
    }

    /**
     * @param ClassMethod $method
     * @param array       $throwParts
     *
     * @return bool
     */
    private function isThrowAnnotated(ClassMethod $method, array $throwParts):bool
    {
        dump($method->getDocComment(), get_class($method));
        return false;
    }

    /**
     * From this method documentation is generated.
     */
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Change method calls from set* to change*.', [
                                                           new CodeSample(
                                                           // code before
                                                               '$user->setPassword("123456");',
                                                               // code after
                                                               '$user->changePassword("123456");'
                                                           ),
                                                       ]
        );
    }
}
