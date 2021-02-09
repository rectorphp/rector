<?php

declare(strict_types=1);

namespace Rector\NetteToSymfony\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Namespace_;
use Rector\Core\Rector\AbstractRector;
use Rector\FileSystemRector\ValueObject\AddedFileWithNodes;
use Rector\NetteToSymfony\Collector\OnFormVariableMethodCallsCollector;
use Rector\NetteToSymfony\NodeFactory\BuildFormClassMethodFactory;
use Rector\NetteToSymfony\NodeFactory\SymfonyControllerFactory;
use Rector\NetteToSymfony\NodeFactory\SymfonyMethodCallsFactory;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ExtraFileCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see https://symfony.com/doc/current/forms.html#creating-form-classes
 *
 * @see \Rector\NetteToSymfony\Tests\Rector\Class_\FormControlToControllerAndFormTypeRector\FormControlToControllerAndFormTypeRectorTest
 */
final class FormControlToControllerAndFormTypeRector extends AbstractRector
{
    /**
     * @var OnFormVariableMethodCallsCollector
     */
    private $onFormVariableMethodCallsCollector;

    /**
     * @var SymfonyControllerFactory
     */
    private $symfonyControllerFactory;

    /**
     * @var BuildFormClassMethodFactory
     */
    private $buildFormClassMethodFactory;

    /**
     * @var SymfonyMethodCallsFactory
     */
    private $symfonyMethodCallsFactory;

    public function __construct(
        OnFormVariableMethodCallsCollector $onFormVariableMethodCallsCollector,
        SymfonyControllerFactory $symfonyControllerFactory,
        BuildFormClassMethodFactory $buildFormClassMethodFactory,
        SymfonyMethodCallsFactory $symfonyMethodCallsFactory

    ) {
        $this->onFormVariableMethodCallsCollector = $onFormVariableMethodCallsCollector;
        $this->symfonyControllerFactory = $symfonyControllerFactory;
        $this->buildFormClassMethodFactory = $buildFormClassMethodFactory;
        $this->symfonyMethodCallsFactory = $symfonyMethodCallsFactory;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change Form that extends Control to Controller and decoupled FormType', [
            new ExtraFileCodeSample(
                <<<'CODE_SAMPLE'
use Nette\Application\UI\Form;
use Nette\Application\UI\Control;

class SomeForm extends Control
{
    public function createComponentForm()
    {
        $form = new Form();
        $form->addText('name', 'Your name');

        $form->onSuccess[] = [$this, 'processForm'];
    }

    public function processForm(Form $form)
    {
        // process me
    }
}
CODE_SAMPLE
,
                <<<'CODE_SAMPLE'
class SomeFormController extends \Symfony\Bundle\FrameworkBundle\Controller\AbstractController
{
    /**
     * @Route(...)
     */
    public function actionSomeForm(\Symfony\Component\HttpFoundation\Request $request): \Symfony\Component\HttpFoundation\Response
    {
        $form = $this->createForm(SomeFormType::class);
        $form->handleRequest($request);

        if ($form->isSuccess() && $form->isValid()) {
            // process me
        }
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
<?php

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class SomeFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $formBuilder, array $options)
    {
        $formBuilder->add('name', TextType::class, [
            'label' => 'Your name'
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
        return [Class_::class];
    }

    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isObjectType($node, 'Nette\Application\UI\Control')) {
            return null;
        }

        foreach ($node->getMethods() as $classMethod) {
            if (! $this->isName($classMethod->name, 'createComponent*')) {
                continue;
            }

            $formTypeClass = $this->collectFormMethodCallsAndCreateFormTypeClass($classMethod);
            if (! $formTypeClass instanceof \PhpParser\Node\Stmt\Class_) {
                continue;
            }

            $symfonyControllerNamespace = $this->symfonyControllerFactory->createNamespace($node, $formTypeClass);
            if (! $symfonyControllerNamespace instanceof Namespace_) {
                continue;
            }

            $addedFileWithNodes = new AddedFileWithNodes('src/Controller/SomeFormController.php', [
                $symfonyControllerNamespace,
            ]);
            $this->removedAndAddedFilesCollector->addAddedFile($addedFileWithNodes);

            return $formTypeClass;
        }

        return $node;
    }

    private function collectFormMethodCallsAndCreateFormTypeClass(ClassMethod $classMethod): ?Class_
    {
        $onFormVariableMethodCalls = $this->onFormVariableMethodCallsCollector->collectFromClassMethod(
            $classMethod
        );

        if ($onFormVariableMethodCalls === []) {
            return null;
        }

        $formBuilderVariable = new Variable('formBuilder');

        // public function buildForm(\Symfony\Component\Form\FormBuilderInterface $formBuilder, array $options)
        $buildFormClassMethod = $this->buildFormClassMethodFactory->create($formBuilderVariable);

        $symfonyMethodCalls = $this->symfonyMethodCallsFactory->create(
            $onFormVariableMethodCalls,
            $formBuilderVariable
        );
        $buildFormClassMethod->stmts = $symfonyMethodCalls;

        return $this->createFormTypeClassFromBuildFormClassMethod($buildFormClassMethod);
    }

    private function createFormTypeClassFromBuildFormClassMethod(ClassMethod $buildFormClassMethod): Class_
    {
        $formTypeClass = new Class_('SomeFormType');
        $formTypeClass->extends = new FullyQualified('Symfony\Component\Form\AbstractType');

        $formTypeClass->stmts[] = $buildFormClassMethod;

        return $formTypeClass;
    }
}
