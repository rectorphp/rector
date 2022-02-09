<?php

declare (strict_types=1);
namespace Rector\Nette\Rector\Class_;

use RectorPrefix20220209\Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PHPStan\Type\ObjectType;
use PHPStan\Type\VerbosityLevel;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\FileSystemRector\ValueObject\AddedFileWithContent;
use Rector\Nette\NodeFactory\ClassWithPublicPropertiesFactory;
use Rector\Nette\ValueObject\LatteVariableType;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix20220209\Webmozart\Assert\Assert;
/**
 * @see \Rector\Nette\Tests\Rector\Class_\TemplateTypeBasedOnPresenterTemplateParametersRector\TemplateTypeBasedOnPresenterTemplateParametersRectorTest
 */
final class TemplateTypeBasedOnPresenterTemplateParametersRector extends \Rector\Core\Rector\AbstractRector implements \Rector\Core\Contract\Rector\ConfigurableRectorInterface
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
    public function __construct(\Rector\Nette\NodeFactory\ClassWithPublicPropertiesFactory $classWithPublicPropertiesFactory)
    {
        $this->classWithPublicPropertiesFactory = $classWithPublicPropertiesFactory;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Creates Template class and adds latte {templateType} based on presenter $this->template parameters', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample(<<<'CODE_SAMPLE'
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
        return [\PhpParser\Node\Stmt\Class_::class];
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
        if (isset($configuration[self::TEMPLATE_CLASS_PARENT])) {
            $templateClassParent = $configuration[self::TEMPLATE_CLASS_PARENT];
            \RectorPrefix20220209\Webmozart\Assert\Assert::string($templateClassParent);
            $this->templateClassParent = $templateClassParent;
        }
        if (isset($configuration[self::TEMPLATE_CLASS_TRAITS])) {
            $templateClassTraits = $configuration[self::TEMPLATE_CLASS_TRAITS];
            \RectorPrefix20220209\Webmozart\Assert\Assert::isArray($templateClassTraits);
            $this->templateClassTraits = $templateClassTraits;
        }
    }
    /**
     * @param Class_ $node
     */
    public function refactor(\PhpParser\Node $node)
    {
        if (!$this->nodeTypeResolver->isObjectType($node, new \PHPStan\Type\ObjectType('Nette\\Application\\UI\\Presenter'))) {
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
    private function findVarTypesForAction(\PhpParser\Node\Stmt\ClassMethod $method) : array
    {
        $varTypes = [];
        $stmts = $method->getStmts();
        if ($stmts === null) {
            return [];
        }
        foreach ($stmts as $stmt) {
            if (!$stmt instanceof \PhpParser\Node\Stmt\Expression) {
                continue;
            }
            if (!$stmt->expr instanceof \PhpParser\Node\Expr\Assign) {
                continue;
            }
            if (!$stmt->expr->var instanceof \PhpParser\Node\Expr\PropertyFetch) {
                continue;
            }
            /** @var PropertyFetch $propertyFetch */
            $propertyFetch = $stmt->expr->var;
            if (!$this->isName($propertyFetch->var, 'template')) {
                continue;
            }
            $staticType = $this->getType($stmt->expr->expr);
            $varTypes[] = new \Rector\Nette\ValueObject\LatteVariableType((string) $this->getName($propertyFetch->name), $staticType->describe(\PHPStan\Type\VerbosityLevel::typeOnly()));
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
            $addedFileWithContent = new \Rector\FileSystemRector\ValueObject\AddedFileWithContent($templateFilePath, $content);
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
        $fullTemplateClassName = '\\' . \RectorPrefix20220209\Nette\Utils\Strings::replace($fullPresenterName, $presenterPattern, $upperCasedActionName . 'Template');
        $templateClass = $this->classWithPublicPropertiesFactory->createNode($fullTemplateClassName, $properties, $this->templateClassParent, $this->templateClassTraits);
        $printedClassContent = "<?php\n\n" . $this->betterStandardPrinter->print($templateClass) . "\n";
        $smartFileInfo = $this->file->getSmartFileInfo();
        $targetFilePath = $smartFileInfo->getRealPathDirectory() . '/' . $templateClassName . '.php';
        $addedFileWithContent = new \Rector\FileSystemRector\ValueObject\AddedFileWithContent($targetFilePath, $printedClassContent);
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
