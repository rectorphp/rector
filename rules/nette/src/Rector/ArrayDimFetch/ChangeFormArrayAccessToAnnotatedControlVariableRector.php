<?php

declare(strict_types=1);

namespace Rector\Nette\Rector\ArrayDimFetch;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Expression;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see \Rector\Nette\Tests\Rector\ArrayDimFetch\ChangeFormArrayAccessToAnnotatedControlVariableRector\ChangeFormArrayAccessToAnnotatedControlVariableRectorTest
 */
final class ChangeFormArrayAccessToAnnotatedControlVariableRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change array access magic on $form to explicit standalone typed variable', [
            new CodeSample(
                <<<'PHP'
use Nette\Application\UI\Form;

class SomePresenter
{
    public function run()
    {
        $form = new Form();
        $this->addText('email', 'Email');

        $form['email']->value = 'hey@hi.hello';
    }
}
PHP
,
                <<<'PHP'
use Nette\Application\UI\Form;

class SomePresenter
{
    public function run()
    {
        $form = new Form();
        $this->addText('email', 'Email');

        /** @var \Nette\Forms\Controls\BaseControl $emailControl */
        $emailControl = $form['email'];
        $emailControl->value = 'hey@hi.hello';
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
        return [ArrayDimFetch::class];
    }

    /**
     * @param ArrayDimFetch $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $node->var instanceof Variable) {
            return null;
        }

        if (! $this->isObjectType($node->var, 'Nette\Application\UI\Form')) {
            return null;
        }

        if (! $node->dim instanceof String_) {
            return null;
        }

        $inputName = $this->getValue($node->dim);
        $controlName = $inputName . 'Control';

        $controlVariableToFormDimFetchAssign = new Assign(new Variable($controlName), clone $node);
        $assignExpression = new Expression($controlVariableToFormDimFetchAssign);

        $this->addVarTag($controlVariableToFormDimFetchAssign, $assignExpression, $controlName);

        $this->addNodeBeforeNode($assignExpression, $node);

        return new Variable($controlName);
    }

    private function addVarTag(Assign $assign, Expression $assignExpression, string $controlName): PhpDocInfo
    {
        $phpDocInfo = $this->phpDocInfoFactory->createEmpty($assignExpression);
        $identifierTypeNode = new IdentifierTypeNode('\Nette\Forms\Controls\BaseControl');

        $varTagValueNode = new VarTagValueNode($identifierTypeNode, '$' . $controlName, '');
        $phpDocInfo->addTagValueNode($varTagValueNode);
        $phpDocInfo->makeSingleLined();

        $assign->setAttribute(AttributeKey::PHP_DOC_INFO, $phpDocInfo);

        return $phpDocInfo;
    }
}
