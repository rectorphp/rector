<?php

declare (strict_types=1);
namespace Rector\Nette\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PHPStan\Type\ObjectType;
use PHPStan\Type\VerbosityLevel;
use Rector\Core\Application\FileSystem\RemovedAndAddedFilesCollector;
use Rector\Core\Rector\AbstractRector;
use Rector\FileSystemRector\ValueObject\AddedFileWithContent;
use Rector\Nette\ValueObject\LatteVariableType;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Nette\Tests\Rector\Class_\LatteVarTypesBasedOnPresenterTemplateParametersRector\LatteVarTypesBasedOnPresenterTemplateParametersRectorTest
 */
final class LatteVarTypesBasedOnPresenterTemplateParametersRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Core\Application\FileSystem\RemovedAndAddedFilesCollector
     */
    private $removedAndAddedFilesCollector;
    public function __construct(\Rector\Core\Application\FileSystem\RemovedAndAddedFilesCollector $removedAndAddedFilesCollector)
    {
        $this->removedAndAddedFilesCollector = $removedAndAddedFilesCollector;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Adds latte {varType}s based on presenter $this->template parameters', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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

// templates/Some/default.latte
{varType string $title}
{varType int $count}

<h1>{$title}</h1>
<span class="count">{$count}</span>
CODE_SAMPLE
)]);
    }
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\Class_::class];
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
        $this->printVarTypesToTemplateFiles($actionVarTypes, $presenterName);
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
    private function printVarTypesToTemplateFiles(array $actionVarTypes, string $presenterName) : void
    {
        foreach ($actionVarTypes as $actionName => $varTypes) {
            if ($varTypes === []) {
                continue;
            }
            $templateFilePath = $this->findTemplateFilePath($presenterName, $actionName);
            if ($templateFilePath === null) {
                continue;
            }
            $content = \file_get_contents($templateFilePath);
            $varTypeContentParts = [];
            foreach ($varTypes as $varType) {
                $varTypeContentParts[] = '{varType ' . $varType->getType() . ' $' . $varType->getName() . '}';
            }
            $content = \implode("\n", $varTypeContentParts) . "\n\n" . $content;
            $addedFileWithContent = new \Rector\FileSystemRector\ValueObject\AddedFileWithContent($templateFilePath, $content);
            $this->removedAndAddedFilesCollector->addAddedFile($addedFileWithContent);
        }
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
