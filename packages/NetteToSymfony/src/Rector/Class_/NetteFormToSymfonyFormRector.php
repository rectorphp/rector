<?php declare(strict_types=1);

namespace Rector\NetteToSymfony\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\String_;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see https://doc.nette.org/en/2.4/forms
 * ↓
 * @see https://symfony.com/doc/current/forms.html
 *
 * @see https://github.com/nette/forms/blob/master/src/Forms/Container.php
 * ↓
 * @see https://github.com/symfony/symfony/tree/master/src/Symfony/Component/Form/Extension/Core/Type
 */
final class NetteFormToSymfonyFormRector extends AbstractRector
{
    /**
     * @var string
     */
    private $presenterClass;

    /**
     * @var string[]
     */
    private $addMethodToFormType = [
        'addText' => 'Symfony\Component\Form\Extension\Core\Type\TextType',
        'addPassword' => 'Symfony\Component\Form\Extension\Core\Type\PasswordType',
        'addTextArea' => 'Symfony\Component\Form\Extension\Core\Type\TextareaType',
        'addEmail' => 'Symfony\Component\Form\Extension\Core\Type\EmailType',
        'addInteger' => 'Symfony\Component\Form\Extension\Core\Type\IntegerType',
        'addHidden' => 'Symfony\Component\Form\Extension\Core\Type\HiddenType',
        'addRadioList' => 'Symfony\Component\Form\Extension\Core\Type\RadioType',
        // https://symfony.com/doc/current/reference/forms/types/checkbox.html
        'addCheckbox' => 'Symfony\Component\Form\Extension\Core\Type\CheckboxType',

        'addUpload' => 'Symfony\Component\Form\Extension\Core\Type\FileType',
        // @todo
        //        'addImage' => 'Symfony\Component\Form\Extension\Core\Type\FileType',

        // @todo
        // https://symfony.com/doc/current/reference/forms/types/choice.html#select-tag-checkboxes-or-radio-buttons
        'addSelect' => 'Symfony\Component\Form\Extension\Core\Type\ChoiceType',
        'addSelectList' => 'Symfony\Component\Form\Extension\Core\Type\ChoiceType',
        'addCheckboxList' => 'Symfony\Component\Form\Extension\Core\Type\ChoiceType',
        'addMultiSelect' => 'Symfony\Component\Form\Extension\Core\Type\ChoiceType',

        // @todo
        // 'addMultiUpload' => 'Symfony\Component\Form\Extension\Core\Type\FileType',

        'addSubmit' => 'Symfony\Component\Form\Extension\Core\Type\SubmitType',
        'addButton' => 'Symfony\Component\Form\Extension\Core\Type\ButtonType',
    ];

    public function __construct(string $presenterClass = 'Nette\Application\IPresenter')
    {
        $this->presenterClass = $presenterClass;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Migrate Nette\Forms in Presenter to Symfony', [
            new CodeSample(
                <<<'CODE_SAMPLE'
use Nette\Application\UI;

class SomePresenter extends UI\Presenter
{
    public function someAction()
    {
        $form = new UI\Form;
        $form->addText('name', 'Name:');
        $form->addPassword('password', 'Password:');
        $form->addSubmit('login', 'Sign up');
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
use Nette\Application\UI;

class SomePresenter extends UI\Presenter
{
    public function someAction()
    {
        $form = $this->createFormBuilder();
        $form->add('name', \Symfony\Component\Form\Extension\Core\Type\TextType::class, [
            'label' => 'Name:'
        ]);
        $form->add('password', \Symfony\Component\Form\Extension\Core\Type\PasswordType::class, [
            'label' => 'Password:'
        ]);
        $form->add('login', \Symfony\Component\Form\Extension\Core\Type\SubmitType::class, [
            'label' => 'Sign up'
        ]);
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
        return [New_::class, MethodCall::class];
    }

    /**
     * @param New_|MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        $classNode = $node->getAttribute(Attribute::CLASS_NODE);
        if ($classNode === null) {
            return null;
        }

        if (! $this->isType($classNode, $this->presenterClass)) {
            return null;
        }

        if ($node instanceof New_) {
            return $this->processNew($node);
        }

        /** @var MethodCall $node */
        if (! $this->isType($node->var, 'Nette\Application\UI\Form')) {
            return null;
        }

        foreach ($this->addMethodToFormType as $method => $classType) {
            if (! $this->isName($node, $method)) {
                continue;
            }

            $node->name = new Identifier('add');

            // remove unused params
            if ($method === 'addText') {
                unset($node->args[3], $node->args[4]);
            }

            // has label
            if (isset($node->args[1])) {
                $labelArray = new Array_([new ArrayItem($node->args[1]->value, new String_('label'))]);
                $node->args[2] = new Arg($labelArray);
            }

            $node->args[1]->value = $this->createClassConstantReference($classType);
        }

        return $node;
    }

    private function processNew(New_ $new): ?MethodCall
    {
        if (! $this->isName($new->class, 'Nette\Application\UI\Form')) {
            return null;
        }

        return new MethodCall(new Variable('this'), 'createFormBuilder');
    }
}
