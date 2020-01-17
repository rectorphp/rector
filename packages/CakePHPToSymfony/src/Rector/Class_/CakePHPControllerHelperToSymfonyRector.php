<?php

declare(strict_types=1);

namespace Rector\CakePHPToSymfony\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use Rector\Exception\ShouldNotHappenException;
use Rector\PhpParser\Node\Manipulator\ClassManipulator;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see https://book.cakephp.org/2/en/views/helpers.html
 * @see https://book.cakephp.org/2/en/core-libraries/helpers/flash.html
 * @see https://symfony.com/doc/5.0/controller.html#flash-messages
 *
 * @see \Rector\CakePHPToSymfony\Tests\Rector\Class_\CakePHPControllerHelperToSymfonyRector\CakePHPControllerHelperToSymfonyRectorTest
 */
final class CakePHPControllerHelperToSymfonyRector extends AbstractRector
{
    /**
     * @var ClassManipulator
     */
    private $classManipulator;

    public function __construct(ClassManipulator $classManipulator)
    {
        $this->classManipulator = $classManipulator;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Migrate CakePHP 2.4 Controller $helpers and $components property to Symfony 5', [
            new CodeSample(
                <<<'PHP'
class HomepageController extends AppController
{
    public $helpers = ['Flash'];

    public function index()
    {
        $this->Flash->success(__('Your post has been saved.'));
        $this->Flash->error(__('Unable to add your post.'));
    }
}
PHP
,
                <<<'PHP'
class HomepageController extends AppController
{
    public function index()
    {
        $this->addFlash('success', __('Your post has been saved.'));
        $this->addFlash('error', __('Unable to add your post.'));
    }
}
PHP

            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }

    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isObjectType($node, 'AppController')) {
            return null;
        }

        $helpersProperty = $this->classManipulator->getProperty($node, 'helpers');
        if ($helpersProperty === null) {
            return null;
        }

        // remove $helpers completely
        $this->removeNode($helpersProperty);

        // replace $this->Flash->.. → $this->addFlash(...)
        foreach ($node->getMethods() as $classMethod) {
            $this->traverseNodesWithCallable((array) $classMethod->stmts, function (Node $node) {
                if (! $this->isThisFlashMethodCall($node)) {
                    return null;
                }

                /** @var MethodCall $node */
                $message = $node->args[0]->value;

                $kind = $this->getName($node->name);
                if ($kind === null) {
                    throw new ShouldNotHappenException();
                }

                $thisVariable = new Variable('this');

                $args = [new Arg(new String_($kind)), new Arg($message)];

                return new MethodCall($thisVariable, 'addFlash', $args);
            });
        }

        return $node;
    }

    private function isThisFlashMethodCall(Node $node): bool
    {
        if (! $node instanceof MethodCall) {
            return false;
        }

        if (! $node->var instanceof PropertyFetch) {
            return false;
        }

        if (! $this->isName($node->var->var, 'this')) {
            return false;
        }

        if (! $this->isName($node->var->name, 'Flash')) {
            return false;
        }

        return true;
    }
}
