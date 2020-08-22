<?php

declare(strict_types=1);

namespace Rector\RectorGenerator\Config;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use Rector\Core\PhpParser\Parser\Parser;
use Rector\Core\PhpParser\Printer\BetterStandardPrinter;
use Rector\RectorGenerator\Rector\Closure\AddNewServiceToSymfonyPhpConfigRector;
use Rector\RectorGenerator\TemplateFactory;
use Rector\RectorGenerator\ValueObject\RectorRecipe;
use Symplify\SmartFileSystem\SmartFileInfo;
use Symplify\SmartFileSystem\SmartFileSystem;

final class ConfigFilesystem
{
    /**
     * @var string
     */
    public const RECTOR_FQN_NAME_PATTERN = 'Rector\__Package__\Rector\__Category__\__Name__';

    /**
     * @var TemplateFactory
     */
    private $templateFactory;

    /**
     * @var Parser
     */
    private $parser;

    /**
     * @var AddNewServiceToSymfonyPhpConfigRector
     */
    private $addNewServiceToSymfonyPhpConfigRector;

    /**
     * @var BetterStandardPrinter
     */
    private $betterStandardPrinter;

    /**
     * @var SmartFileSystem
     */
    private $smartFileSystem;

    public function __construct(
        AddNewServiceToSymfonyPhpConfigRector $addNewServiceToSymfonyPhpConfigRector,
        BetterStandardPrinter $betterStandardPrinter,
        Parser $parser,
        SmartFileSystem $smartFileSystem,
        TemplateFactory $templateFactory
    ) {
        $this->templateFactory = $templateFactory;
        $this->parser = $parser;
        $this->addNewServiceToSymfonyPhpConfigRector = $addNewServiceToSymfonyPhpConfigRector;
        $this->betterStandardPrinter = $betterStandardPrinter;
        $this->smartFileSystem = $smartFileSystem;
    }

    /**
     * @param string[] $templateVariables
     */
    public function appendRectorServiceToSet(RectorRecipe $rectorRecipe, array $templateVariables): void
    {
        if ($rectorRecipe->getSet() === null) {
            return;
        }

        $setFilePath = $rectorRecipe->getSet();
        $setFileInfo = new SmartFileInfo($setFilePath);
        $setFileContents = $setFileInfo->getContents();

        // already added?
        $rectorFqnName = $this->templateFactory->create(self::RECTOR_FQN_NAME_PATTERN, $templateVariables);
        if (Strings::contains($setFileContents, $rectorFqnName)) {
            return;
        }

        // 1. parse the file
        $setConfigNodes = $this->parser->parseFileInfo($setFileInfo);

        // 2. add the set() call
        $this->decorateNamesToFullyQualified($setConfigNodes);

        $nodeTraverser = new NodeTraverser();

        $this->addNewServiceToSymfonyPhpConfigRector->setRectorClass($rectorFqnName);
        $nodeTraverser->addVisitor($this->addNewServiceToSymfonyPhpConfigRector);
        $setConfigNodes = $nodeTraverser->traverse($setConfigNodes);

        // 3. print the content back to file
        $changedSetConfigContent = $this->betterStandardPrinter->prettyPrintFile($setConfigNodes);
        $this->smartFileSystem->dumpFile($setFileInfo->getRealPath(), $changedSetConfigContent);
    }

    /**
     * @param Node[] $nodes
     */
    private function decorateNamesToFullyQualified(array $nodes): void
    {
        // decorate nodes with names first
        $nameResolverNodeTraverser = new NodeTraverser();
        $nameResolverNodeTraverser->addVisitor(new NameResolver());
        $nameResolverNodeTraverser->traverse($nodes);
    }
}
