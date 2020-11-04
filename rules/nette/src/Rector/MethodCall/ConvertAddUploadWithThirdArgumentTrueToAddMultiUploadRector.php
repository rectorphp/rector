<?php

declare(strict_types=1);

namespace Rector\Nette\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\String_;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use PhpParser\Node\Expr\ConstFetch;

/**
 * @see \Rector\Nette\Tests\Rector\MethodCall\ConvertAddUploadWithThirdArgumentTrueToAddMultiUploadRector\ConvertAddUploadWithThirdArgumentTrueToAddMultiUploadRectorTest
 */
final class ConvertAddUploadWithThirdArgumentTrueToAddMultiUploadRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('convert addUpload() with 3rd argument true to addMultiUpload()', [
            new CodeSample(
                <<<'CODE_SAMPLE'
$form = new Nette\Forms\Form();
$form->addUpload('...', '...', true);
CODE_SAMPLE

                ,
                <<<'CODE_SAMPLE'
$form = new Nette\Forms\Form();
$form->addMultiUpload('...', '...');
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
        if (! $this->isObjectType($node->var, 'Nette\Forms\Form')) {
            return null;
        }

        if (! $this->isName($node->name, 'addUpload')) {
            return null;
        }

        $args = $node->args;
        if (! isset($args[2])) {
            return null;
        }

        $thirdArgumentValue = $node->args[2]->value;
        if ($thirdArgumentValue instanceof ConstFetch && $thirdArgumentValue->name->parts[0] === 'true') {
            $node->name = 'addMultiUpload';
            unset($node->args[2]);
            return $node;
        }

        return null;
    }
}
