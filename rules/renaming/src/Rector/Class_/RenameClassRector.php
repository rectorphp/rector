<?php

declare(strict_types=1);

namespace Rector\Renaming\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Property;
use Rector\Core\Configuration\ChangeConfiguration;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Renaming\NodeManipulator\ClassRenamer;

/**
 * @see \Rector\Renaming\Tests\Rector\Class_\RenameClassRector\RenameClassRectorTest
 */
final class RenameClassRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private $oldToNewClasses = [];

    /**
     * @var ClassRenamer
     */
    private $classRenamer;

    /**
     * @param string[] $oldToNewClasses
     */
    public function __construct(
        ChangeConfiguration $changeConfiguration,
        ClassRenamer $classRenamer,
        array $oldToNewClasses = []
    ) {
        $this->oldToNewClasses = $oldToNewClasses;
        $this->classRenamer = $classRenamer;

        if ($oldToNewClasses !== []) {
            $changeConfiguration->setOldToNewClasses($oldToNewClasses);
        }
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Replaces defined classes by new ones.', [
            new ConfiguredCodeSample(
                <<<'PHP'
namespace App;

use SomeOldClass;

function someFunction(SomeOldClass $someOldClass): SomeOldClass
{
    if ($someOldClass instanceof SomeOldClass) {
        return new SomeOldClass;
    }
}
PHP
                ,
                <<<'PHP'
namespace App;

use SomeNewClass;

function someFunction(SomeNewClass $someOldClass): SomeNewClass
{
    if ($someOldClass instanceof SomeNewClass) {
        return new SomeNewClass;
    }
}
PHP
                ,
                [
                    '$oldToNewClasses' => [
                        'App\SomeOldClass' => 'App\SomeNewClass',
                    ],
                ]
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [
            Name::class,
            Property::class,
            FunctionLike::class,
            Expression::class,
            ClassLike::class,
            Namespace_::class,
        ];
    }

    /**
     * @param Name|FunctionLike|Property $node
     */
    public function refactor(Node $node): ?Node
    {
        return $this->classRenamer->renameNode($node, $this->oldToNewClasses);
    }
}
