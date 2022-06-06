<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Symfony\Rector\Class_;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Identifier;
use RectorPrefix20220606\PhpParser\Node\Name;
use RectorPrefix20220606\PhpParser\Node\Stmt\Class_;
use RectorPrefix20220606\PhpParser\Node\Stmt\ClassMethod;
use RectorPrefix20220606\PhpParser\Node\Stmt\Namespace_;
use RectorPrefix20220606\Rector\Core\Application\FileSystem\RemovedAndAddedFilesCollector;
use RectorPrefix20220606\Rector\Core\Exception\ShouldNotHappenException;
use RectorPrefix20220606\Rector\Core\PhpParser\Node\CustomNode\FileWithoutNamespace;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Rector\Core\ValueObject\MethodName;
use RectorPrefix20220606\Rector\Symfony\NodeAnalyzer\SymfonyControllerFilter;
use RectorPrefix20220606\Rector\Symfony\NodeFactory\InvokableControllerClassFactory;
use RectorPrefix20220606\Rector\Symfony\Printer\NeighbourClassLikePrinter;
use RectorPrefix20220606\Rector\Symfony\TypeAnalyzer\ControllerAnalyzer;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://symfony.com/doc/2.8/controller/service.html#referring-to-the-service
 *
 * @see \Rector\Symfony\Tests\Rector\Class_\InvokableControllerRector\InvokableControllerRectorTest
 *
 * Inspiration @see https://github.com/rectorphp/rector-src/blob/main/rules/PSR4/Rector/Namespace_/MultipleClassFileToPsr4ClassesRector.php
 */
final class InvokableControllerRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Symfony\TypeAnalyzer\ControllerAnalyzer
     */
    private $controllerAnalyzer;
    /**
     * @readonly
     * @var \Rector\Symfony\NodeAnalyzer\SymfonyControllerFilter
     */
    private $symfonyControllerFilter;
    /**
     * @readonly
     * @var \Rector\Symfony\Printer\NeighbourClassLikePrinter
     */
    private $neighbourClassLikePrinter;
    /**
     * @readonly
     * @var \Rector\Symfony\NodeFactory\InvokableControllerClassFactory
     */
    private $invokableControllerClassFactory;
    /**
     * @readonly
     * @var \Rector\Core\Application\FileSystem\RemovedAndAddedFilesCollector
     */
    private $removedAndAddedFilesCollector;
    public function __construct(ControllerAnalyzer $controllerAnalyzer, SymfonyControllerFilter $symfonyControllerFilter, NeighbourClassLikePrinter $neighbourClassLikePrinter, InvokableControllerClassFactory $invokableControllerClassFactory, RemovedAndAddedFilesCollector $removedAndAddedFilesCollector)
    {
        $this->controllerAnalyzer = $controllerAnalyzer;
        $this->symfonyControllerFilter = $symfonyControllerFilter;
        $this->neighbourClassLikePrinter = $neighbourClassLikePrinter;
        $this->invokableControllerClassFactory = $invokableControllerClassFactory;
        $this->removedAndAddedFilesCollector = $removedAndAddedFilesCollector;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change god controller to single-action invokable controllers', [new CodeSample(<<<'CODE_SAMPLE'
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

final class SomeController extends Controller
{
    public function detailAction()
    {
    }

    public function listAction()
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

final class SomeDetailController extends Controller
{
    public function __invoke()
    {
    }
}

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

final class SomeListController extends Controller
{
    public function __invoke()
    {
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        // skip anonymous controllers
        if (!$node->name instanceof Identifier) {
            return null;
        }
        if (!$this->controllerAnalyzer->isInsideController($node)) {
            return null;
        }
        $actionClassMethods = $this->symfonyControllerFilter->filterActionMethods($node);
        if ($actionClassMethods === []) {
            return null;
        }
        // 1. single action method → only rename
        if (\count($actionClassMethods) === 1) {
            return $this->refactorSingleAction($actionClassMethods[0], $node);
        }
        // 2. multiple action methods → split + rename current based on action name
        foreach ($actionClassMethods as $actionClassMethod) {
            $invokableControllerClass = $this->invokableControllerClassFactory->createWithActionClassMethod($node, $actionClassMethod);
            /** @var Namespace_|FileWithoutNamespace|null $parentNamespace */
            $parentNamespace = $this->betterNodeFinder->findParentByTypes($node, [Namespace_::class, FileWithoutNamespace::class]);
            if (!$parentNamespace instanceof Node) {
                throw new ShouldNotHappenException('Missing parent namespace or without namespace node');
            }
            $this->neighbourClassLikePrinter->printClassLike($invokableControllerClass, $parentNamespace, $this->file->getSmartFileInfo(), $this->file);
        }
        // remove original file
        $smartFileInfo = $this->file->getSmartFileInfo();
        $this->removedAndAddedFilesCollector->removeFile($smartFileInfo);
        return null;
    }
    private function refactorSingleAction(ClassMethod $actionClassMethod, Class_ $class) : Class_
    {
        $actionClassMethod->name = new Identifier(MethodName::INVOKE);
        return $class;
    }
}
