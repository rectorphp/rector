<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Tests\PhpDocParser\TagValueNodeReprint;

use Iterator;
use PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\TemplateTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Class_\EntityTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Class_\TableTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Property_\ColumnTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Property_\CustomIdGeneratorTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Property_\GeneratedValueTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Property_\JoinTableTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNode\Gedmo\BlameableTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNode\Gedmo\SlugTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNode\Sensio\SensioMethodTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNode\Sensio\SensioTemplateTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNode\Symfony\SymfonyRouteTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNode\Symfony\Validator\Constraints\AssertChoiceTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNode\Symfony\Validator\Constraints\AssertTypeTagValueNode;
use Rector\BetterPhpDocParser\Tests\PhpDocParser\AbstractPhpDocInfoTest;

final class TagValueNodeReprintTest extends AbstractPhpDocInfoTest
{
    /**
     * @dataProvider provideData()
     * @param class-string $tagValueNodeClass
     */
    public function test(string $filePath, string $tagValueNodeClass): void
    {
        $this->doTestPrintedPhpDocInfo($filePath, $tagValueNodeClass);
    }

    public function provideData(): Iterator
    {
        foreach ($this->getDirectoriesByTagValueNodes() as $tagValueNode => $directory) {
            foreach ($this->findFilesFromDirectory($directory) as $filePath) {
                yield [$filePath, $tagValueNode];
            }
        }
    }

    /**
     * @return string[]
     */
    private function getDirectoriesByTagValueNodes(): array
    {
        return [
            BlameableTagValueNode::class => __DIR__ . '/Fixture/Blameable',
            SlugTagValueNode::class => __DIR__ . '/Fixture/Gedmo',
            AssertChoiceTagValueNode::class => __DIR__ . '/Fixture/AssertChoice',
            AssertTypeTagValueNode::class => __DIR__ . '/Fixture/AssertType',
            SymfonyRouteTagValueNode::class => __DIR__ . '/Fixture/SymfonyRoute',
            // Doctrine
            ColumnTagValueNode::class => __DIR__ . '/Fixture/DoctrineColumn',
            JoinTableTagValueNode::class => __DIR__ . '/Fixture/DoctrineJoinTable',
            EntityTagValueNode::class => __DIR__ . '/Fixture/DoctrineEntity',
            TableTagValueNode::class => __DIR__ . '/Fixture/DoctrineTable',
            CustomIdGeneratorTagValueNode::class => __DIR__ . '/Fixture/DoctrineCustomIdGenerator',
            GeneratedValueTagValueNode::class => __DIR__ . '/Fixture/DoctrineGeneratedValue',

            // special case
            GenericTagValueNode::class => __DIR__ . '/Fixture/ConstantReference',
            SensioTemplateTagValueNode::class => __DIR__ . '/Fixture/SensioTemplate',
            SensioMethodTagValueNode::class => __DIR__ . '/Fixture/SensioMethod',
            TemplateTagValueNode::class => __DIR__ . '/Fixture/Native/Template',
            VarTagValueNode::class => __DIR__ . '/Fixture/Native/VarTag',
        ];
    }
}
