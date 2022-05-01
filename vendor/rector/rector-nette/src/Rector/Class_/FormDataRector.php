<?php

declare (strict_types=1);
namespace Rector\Nette\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use Rector\Core\Application\FileSystem\RemovedAndAddedFilesCollector;
use Rector\Core\Contract\PhpParser\NodePrinterInterface;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\FileSystemRector\ValueObject\AddedFileWithContent;
use Rector\Nette\NodeFactory\ClassWithPublicPropertiesFactory;
use Rector\Nette\NodeFinder\FormFieldsFinder;
use Rector\Nette\NodeFinder\FormOnSuccessCallbackFinder;
use Rector\Nette\NodeFinder\FormOnSuccessCallbackValuesParamFinder;
use Rector\Nette\NodeFinder\FormVariableFinder;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix20220501\Webmozart\Assert\Assert;
/**
 * @see https://doc.nette.org/en/3.1/form-presenter#toc-mapping-to-classes
 * @see \Rector\Nette\Tests\Rector\Class_\FormDataRector\FormDataRectorTest
 */
final class FormDataRector extends \Rector\Core\Rector\AbstractRector implements \Rector\Core\Contract\Rector\ConfigurableRectorInterface
{
    public const FORM_DATA_CLASS_PARENT = 'form_data_class_parent';
    public const FORM_DATA_CLASS_TRAITS = 'form_data_class_traits';
    /**
     * @var string
     */
    private $formDataClassParent = 'Nette\\Utils\\ArrayHash';
    /**
     * @var string[]
     */
    private $formDataClassTraits = ['Nette\\SmartObject'];
    /**
     * @readonly
     * @var \Rector\Nette\NodeFinder\FormVariableFinder
     */
    private $formVariableFinder;
    /**
     * @readonly
     * @var \Rector\Nette\NodeFinder\FormFieldsFinder
     */
    private $formFieldsFinder;
    /**
     * @readonly
     * @var \Rector\Nette\NodeFinder\FormOnSuccessCallbackFinder
     */
    private $formOnSuccessCallbackFinder;
    /**
     * @readonly
     * @var \Rector\Nette\NodeFinder\FormOnSuccessCallbackValuesParamFinder
     */
    private $formOnSuccessCallbackValuesParamFinder;
    /**
     * @readonly
     * @var \Rector\Nette\NodeFactory\ClassWithPublicPropertiesFactory
     */
    private $classWithPublicPropertiesFactory;
    /**
     * @readonly
     * @var \Rector\Core\Contract\PhpParser\NodePrinterInterface
     */
    private $nodePrinter;
    /**
     * @readonly
     * @var \Rector\Core\Application\FileSystem\RemovedAndAddedFilesCollector
     */
    private $removedAndAddedFilesCollector;
    public function __construct(\Rector\Nette\NodeFinder\FormVariableFinder $formVariableFinder, \Rector\Nette\NodeFinder\FormFieldsFinder $formFieldsFinder, \Rector\Nette\NodeFinder\FormOnSuccessCallbackFinder $formOnSuccessCallbackFinder, \Rector\Nette\NodeFinder\FormOnSuccessCallbackValuesParamFinder $formOnSuccessCallbackValuesParamFinder, \Rector\Nette\NodeFactory\ClassWithPublicPropertiesFactory $classWithPublicPropertiesFactory, \Rector\Core\Contract\PhpParser\NodePrinterInterface $nodePrinter, \Rector\Core\Application\FileSystem\RemovedAndAddedFilesCollector $removedAndAddedFilesCollector)
    {
        $this->formVariableFinder = $formVariableFinder;
        $this->formFieldsFinder = $formFieldsFinder;
        $this->formOnSuccessCallbackFinder = $formOnSuccessCallbackFinder;
        $this->formOnSuccessCallbackValuesParamFinder = $formOnSuccessCallbackValuesParamFinder;
        $this->classWithPublicPropertiesFactory = $classWithPublicPropertiesFactory;
        $this->nodePrinter = $nodePrinter;
        $this->removedAndAddedFilesCollector = $removedAndAddedFilesCollector;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Create form data class with all fields of Form', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample(<<<'CODE_SAMPLE'
class MyFormFactory
{
    public function create()
    {
        $form = new Form();

        $form->addText('foo', 'Foo');
        $form->addText('bar', 'Bar')->setRequired();
        $form->onSuccess[] = function (Form $form, ArrayHash $values) {
            // do something
        }
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class MyFormFactoryFormData
{
    public string $foo;
    public string $bar;
}

class MyFormFactory
{
    public function create()
    {
        $form = new Form();

        $form->addText('foo', 'Foo');
        $form->addText('bar', 'Bar')->setRequired();
        $form->onSuccess[] = function (Form $form, MyFormFactoryFormData $values) {
            // do something
        }
    }
}
CODE_SAMPLE
, [self::FORM_DATA_CLASS_PARENT => '', self::FORM_DATA_CLASS_TRAITS => []])]);
    }
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\Class_::class];
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
        if (isset($configuration[self::FORM_DATA_CLASS_PARENT])) {
            $formDataClassParent = $configuration[self::FORM_DATA_CLASS_PARENT];
            \RectorPrefix20220501\Webmozart\Assert\Assert::string($formDataClassParent);
            $this->formDataClassParent = $formDataClassParent;
        }
        if (isset($configuration[self::FORM_DATA_CLASS_TRAITS])) {
            $formDataClassTraits = $configuration[self::FORM_DATA_CLASS_TRAITS];
            \RectorPrefix20220501\Webmozart\Assert\Assert::isArray($formDataClassTraits);
            \RectorPrefix20220501\Webmozart\Assert\Assert::allString($formDataClassTraits);
            $this->formDataClassTraits = $formDataClassTraits;
        }
    }
    /**
     * @param Class_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($node->name === null) {
            return null;
        }
        $shortClassName = $this->nodeNameResolver->getShortName($node);
        $fullClassName = $this->getName($node);
        $form = $this->formVariableFinder->find($node);
        if (!$form instanceof \PhpParser\Node\Expr\Variable) {
            return null;
        }
        $formFields = $this->formFieldsFinder->find($node, $form);
        if ($formFields === []) {
            return null;
        }
        $properties = [];
        foreach ($formFields as $formField) {
            $properties[$formField->getName()] = ['type' => $formField->getType(), 'nullable' => $formField->getType() === 'int' && !$formField->isRequired()];
        }
        $formDataClassName = $shortClassName . 'FormData';
        $fullFormDataClassName = '\\' . $fullClassName . 'FormData';
        $formDataClass = $this->classWithPublicPropertiesFactory->createNode($fullFormDataClassName, $properties, $this->formDataClassParent, $this->formDataClassTraits);
        $printedClassContent = "<?php\n\n" . $this->nodePrinter->print($formDataClass) . "\n";
        $smartFileInfo = $this->file->getSmartFileInfo();
        $targetFilePath = $smartFileInfo->getRealPathDirectory() . '/' . $formDataClassName . '.php';
        $addedFileWithContent = new \Rector\FileSystemRector\ValueObject\AddedFileWithContent($targetFilePath, $printedClassContent);
        $this->removedAndAddedFilesCollector->addAddedFile($addedFileWithContent);
        $onSuccessCallback = $this->formOnSuccessCallbackFinder->find($node, $form);
        if (!$onSuccessCallback instanceof \PhpParser\Node\Expr) {
            return null;
        }
        $valuesParam = $this->formOnSuccessCallbackValuesParamFinder->find($node, $onSuccessCallback);
        if (!$valuesParam instanceof \PhpParser\Node\Param) {
            return null;
        }
        $valuesParam->type = new \PhpParser\Node\Identifier($fullFormDataClassName);
        return $node;
    }
}
