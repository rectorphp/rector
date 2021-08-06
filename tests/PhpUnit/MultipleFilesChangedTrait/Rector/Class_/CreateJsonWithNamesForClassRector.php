<?php

declare(strict_types=1);

namespace Rector\Core\Tests\PhpUnit\MultipleFilesChangedTrait\Rector\Class_;

use Nette\Utils\Json;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Rector\Core\Rector\AbstractRector;
use Rector\FileSystemRector\ValueObject\AddedFileWithContent;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Core\Tests\PhpUnit\MultipleFilesChangedTrait\MultipleFilesChangedTraitTest
 */
final class CreateJsonWithNamesForClassRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Creates json with names for class', []);
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }

    /**
     * @param Class_ $node
     */
    public function refactor(Node $node)
    {
        $smartFileInfo = $this->file->getSmartFileInfo();
        $targetFilePath = $smartFileInfo->getRealPathDirectory() . '/names.json';

        $content = Json::encode([
            'short' => $this->nodeNameResolver->getShortName($node),
            'fqn' => $this->getName($node),
        ], Json::PRETTY);

        $addedFileWithContent = new AddedFileWithContent($targetFilePath, $content);
        $this->removedAndAddedFilesCollector->addAddedFile($addedFileWithContent);
        return null;
    }
}
