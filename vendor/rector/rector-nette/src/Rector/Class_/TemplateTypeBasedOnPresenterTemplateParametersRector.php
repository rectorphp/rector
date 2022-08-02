<?php

declare (strict_types=1);
namespace Rector\Nette\Rector\Class_;

use RectorPrefix202208\Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PHPStan\Type\ObjectType;
use PHPStan\Type\VerbosityLevel;
use Rector\Core\Application\FileSystem\RemovedAndAddedFilesCollector;
use Rector\Core\Contract\PhpParser\NodePrinterInterface;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\FileSystemRector\ValueObject\AddedFileWithContent;
use Rector\Nette\NodeFactory\ClassWithPublicPropertiesFactory;
use Rector\Nette\ValueObject\LatteVariableType;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix202208\Webmozart\Assert\Assert;
/**
 * @see \Rector\Nette\Tests\Rector\Class_\TemplateTypeBasedOnPresenterTemplateParametersRector\TemplateTypeBasedOnPresenterTemplateParametersRectorTest
 */
final class TemplateTypeBasedOnPresenterTemplateParametersRector extends AbstractRector implements ConfigurableRectorInterface
{
    public const TEMPLATE_CLASS_PARENT = 'template_class_parent';
    public const TEMPLATE_CLASS_TRAITS = 'template_class_traits';
    /**
     * @var string
     */
    private $templateClassParent = 'Nette\\Bridges\\ApplicationLatte\\Template';
    /**
     * @var string[]
     */
    private $templateClassTraits = [];
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
    public function __construct(ClassWithPublicPropertiesFactory $classWithPublicPropertiesFactory, NodePrinterInterface $nodePrinter, RemovedAndAddedFilesCollector $removedAndAddedFilesCollector)
    {
        $this->classWithPublicPropertiesFactory = $classWithPublicPropertiesFactory;
        $this->nodePrinter = $nodePrinter;
        $this->removedAndAddedFilesCollector = $removedAndAddedFilesCollector;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Creates Template class and adds latte {templateType} based on presenter $this->template parameters', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
// presenters/SomePresenter.php
<?php

use Nette\Application\UI\Presenter;

class SomePresenter extends Presenter
{
    public function renderDefault(): void
    {
        $this->template->title = 'My title';
        $this->template->count = 123;
    }
}

// templates/Some/default.latte
<h1>{$title}</h1>
<span class="count">{$count}</span>
CODE_SAMPLE
, <<<'CODE_SAMPLE'
// presenters/SomePresenter.php
<?php

use Nette\Application\UI\Presenter;

class SomePresenter extends Presenter
{
    public function renderDefault(): void
    {
        $this->template->title = 'My title';
        $this->template->count = 123;
    }
}

// presenters/SomeDefaultTemplate.php
<?php

use Nette\Bridges\ApplicationLatte\Template;

class SomeDefaultTemplate extends Template
{
    public string $title;
    public int $count;
}

// templates/Some/default.latte
{templateType SomeDefaultTemplate}

<h1>{$title}</h1>
<span class="count">{$count}</span>
CODE_SAMPLE
, [self::TEMPLATE_CLASS_PARENT => '', self::TEMPLATE_CLASS_TRAITS => []])]);
    }
    public function getNodeTypes() : array
    {
        return [Class_::class];
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
        if (isset($configuration[self::TEMPLATE_CLASS_PARENT])) {
            $templateClassParent = $configuration[self::TEMPLATE_CLASS_PARENT];
            Assert::string($templateClassParent);
            $this->templateClassParent = $templateClassParent;
        }
        if (isset($configuration[self::TEMPLATE_CLASS_TRAITS])) {
            $templateClassTraits = $configuration[self::TEMPLATE_CLASS_TRAITS];
            Assert::isArray($templateClassTraits);
            $this->templateClassTraits = $templateClassTraits;
        }
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node)
    {
        if (!$this->nodeTypeResolver->isObjectType($node, new ObjectType('Nette\\Application\\UI\\Presenter'))) {
            return null;
        }
        if ($node->name === null) {
            return null;
        }
        $shortClassName = $this->nodeNameResolver->getShortName($node);
        $fullClassName = $this->nodeNameResolver->getName($node);
        if (!\is_string($fullClassName)) {
            return null;
        }
        $presenterName = \str_replace('Presenter', '', $shortClassName);
        $actionVarTypes = [];
        foreach ($node->getMethods() as $method) {
            $fullActionName = $method->name->name;
            if (\strncmp($fullActionName, 'action', \strlen('action')) !== 0 && \strncmp($fullActionName, 'render', \strlen('render')) !== 0) {
                continue;
            }
            $actionName = \str_replace(['action', 'render'], '', $fullActionName);
            $actionName = \lcfirst($actionName);
            if (!isset($actionVarTypes[$actionName])) {
                $actionVarTypes[$actionName] = [];
            }
            $actionVarTypes[$actionName] = \array_merge($actionVarTypes[$actionName], $this->findVarTypesForAction($method));
        }
        $this->printTemplateTypeToTemplateFiles($actionVarTypes, $presenterName, $fullClassName);
        return null;
    }
    /**
     * @return LatteVariableType[]
     */
    private function findVarTypesForAction(ClassMethod $method) : array
    {
        $varTypes = [];
        $stmts = $method->getStmts();
        if ($stmts === null) {
            return [];
        }
        foreach ($stmts as $stmt) {
            if (!$stmt instanceof Expression) {
                continue;
            }
            if (!$stmt->expr instanceof Assign) {
                continue;
            }
            if (!$stmt->expr->var instanceof PropertyFetch) {
                continue;
            }
            /** @var PropertyFetch $propertyFetch */
            $propertyFetch = $stmt->expr->var;
            if (!$this->isName($propertyFetch->var, 'template')) {
                continue;
            }
            $staticType = $this->getType($stmt->expr->expr);
            $varTypes[] = new LatteVariableType((string) $this->getName($propertyFetch->name), $staticType->describe(VerbosityLevel::typeOnly()));
        }
        return $varTypes;
    }
    /**
     * @param array<string, LatteVariableType[]> $actionVarTypes
     */
    private function printTemplateTypeToTemplateFiles(array $actionVarTypes, string $presenterName, string $fullPresenterName) : void
    {
        foreach ($actionVarTypes as $actionName => $varTypes) {
            if ($varTypes === []) {
                continue;
            }
            $templateFilePath = $this->findTemplateFilePath($presenterName, $actionName);
            if ($templateFilePath === null) {
                continue;
            }
            $templateClassName = $this->createTemplateClass($presenterName, $fullPresenterName, $actionName, $varTypes);
            $content = \file_get_contents($templateFilePath);
            $content = '{templateType ' . \ltrim($templateClassName, '\\') . "}\n\n" . $content;
            $addedFileWithContent = new AddedFileWithContent($templateFilePath, $content);
            $this->removedAndAddedFilesCollector->addAddedFile($addedFileWithContent);
        }
    }
    /**
     * @param LatteVariableType[] $varTypes
     */
    private function createTemplateClass(string $presenterName, string $fullPresenterName, string $actionName, array $varTypes) : string
    {
        $properties = [];
        foreach ($varTypes as $varType) {
            $properties[$varType->getName()] = ['type' => $varType->getType()];
        }
        $upperCasedActionName = \ucfirst($actionName);
        $templateClassName = $presenterName . $upperCasedActionName . 'Template';
        $presenterPattern = '#Presenter$#';
        $fullTemplateClassName = '\\' . Strings::replace($fullPresenterName, $presenterPattern, $upperCasedActionName . 'Template');
        $templateClass = $this->classWithPublicPropertiesFactory->createNode($fullTemplateClassName, $properties, $this->templateClassParent, $this->templateClassTraits);
        $printedClassContent = "<?php\n\n" . $this->nodePrinter->print($templateClass) . "\n";
        $smartFileInfo = $this->file->getSmartFileInfo();
        $targetFilePath = $smartFileInfo->getRealPathDirectory() . '/' . $templateClassName . '.php';
        $addedFileWithContent = new AddedFileWithContent($targetFilePath, $printedClassContent);
        $this->removedAndAddedFilesCollector->addAddedFile($addedFileWithContent);
        return $fullTemplateClassName;
    }
    private function findTemplateFilePath(string $presenterName, string $actionName) : ?string
    {
        $smartFileInfo = $this->file->getSmartFileInfo();
        $dir = $smartFileInfo->getRealPathDirectory();
        $dir = \is_dir("{$dir}/templates") ? $dir : \dirname($dir);
        $templateFileCandidates = ["{$dir}/templates/{$presenterName}/{$actionName}.latte", "{$dir}/templates/{$presenterName}.{$actionName}.latte"];
        foreach ($templateFileCandidates as $templateFileCandidate) {
            if (\file_exists($templateFileCandidate)) {
                return $templateFileCandidate;
            }
        }
        return null;
    }
}
