<?php

declare(strict_types=1);

namespace Rector\Tests\BetterPhpDocParser\PhpDocParser\TagValueNodeReprint;

use Iterator;
<<<<<<< HEAD
=======
use PHPStan\PhpDocParser\Ast\Node;
use PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\TemplateTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use Rector\Doctrine\PhpDoc\Node\Class_\EmbeddedTagValueNode;
use Rector\Doctrine\PhpDoc\Node\Class_\EntityTagValueNode;
use Rector\Doctrine\PhpDoc\Node\Class_\TableTagValueNode;
use Rector\Doctrine\PhpDoc\Node\Gedmo\SlugTagValueNode;
use Rector\Doctrine\PhpDoc\Node\Property_\ColumnTagValueNode;
use Rector\Doctrine\PhpDoc\Node\Property_\CustomIdGeneratorTagValueNode;
use Rector\Doctrine\PhpDoc\Node\Property_\GeneratedValueTagValueNode;
use Rector\Doctrine\PhpDoc\Node\Property_\JoinTableTagValueNode;
use Rector\Symfony\PhpDoc\Node\AssertChoiceTagValueNode;
use Rector\Symfony\PhpDoc\Node\AssertTypeTagValueNode;
use Rector\Symfony\PhpDoc\Node\Sensio\SensioMethodTagValueNode;
use Rector\Symfony\PhpDoc\Node\Sensio\SensioTemplateTagValueNode;
use Rector\Symfony\PhpDoc\Node\SymfonyRouteTagValueNode;
>>>>>>> 4100b04a63... Refactor doctrine/annotation parser to static reflection with phpdoc-parser
use Rector\Tests\BetterPhpDocParser\PhpDocParser\AbstractPhpDocInfoTest;
use Symplify\EasyTesting\FixtureSplitter\TrioFixtureSplitter;
use Symplify\EasyTesting\ValueObject\FixtureSplit\TrioContent;
use Symplify\SmartFileSystem\SmartFileInfo;
use Symplify\SmartFileSystem\SmartFileSystem;

final class TagValueNodeReprintTest extends AbstractPhpDocInfoTest
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $fixtureFileInfo): void
    {
        $trioFixtureSplitter = new TrioFixtureSplitter();
        $trioContent = $trioFixtureSplitter->splitFileInfo($fixtureFileInfo);

        $nodeClass = trim($trioContent->getSecondValue());
        $tagValueNodeClasses = $this->splitListByEOL($trioContent->getExpectedResult());

        $fixtureFileInfo = $this->createFixtureFileInfo($trioContent, $fixtureFileInfo);
        foreach ($tagValueNodeClasses as $tagValueNodeClass) {
            $this->doTestPrintedPhpDocInfo($fixtureFileInfo, $tagValueNodeClass, $nodeClass);
        }
    }

    /**
     * @return Iterator<SmartFileInfo>
     */
    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture', '*.php.inc');
    }

    /**
     * @return string[]
     */
    private function splitListByEOL(string $content): array
    {
<<<<<<< HEAD
        $trimmedContent = trim($content);
        return explode(PHP_EOL, $trimmedContent);
    }

    private function createFixtureFileInfo(TrioContent $trioContent, SmartFileInfo $fixturefileInfo): SmartFileInfo
    {
        $temporaryFileName = sys_get_temp_dir() . '/rector/tests/' . $fixturefileInfo->getRelativePathname();
        $firstValue = $trioContent->getFirstValue();

        $smartFileSystem = new SmartFileSystem();
        $smartFileSystem->dumpFile($temporaryFileName, $firstValue);

        // to make it doctrine/annotation parse-able
        require_once $temporaryFileName;

        return new SmartFileInfo($temporaryFileName);
=======
        return [
            // new approach
            'Gedmo\Mapping\Annotation\Blameable' => __DIR__ . '/Fixture/Blameable',

            //            SlugTagValueNode::class => __DIR__ . '/Fixture/Gedmo',
            //
            //            // symfony
            //            AssertChoiceTagValueNode::class => __DIR__ . '/Fixture/AssertChoice',
            //            AssertTypeTagValueNode::class => __DIR__ . '/Fixture/AssertType',
            //            SymfonyRouteTagValueNode::class => __DIR__ . '/Fixture/SymfonyRoute',
            //
            //            // Doctrine
            //            ColumnTagValueNode::class => __DIR__ . '/Fixture/DoctrineColumn',
            //            JoinTableTagValueNode::class => __DIR__ . '/Fixture/DoctrineJoinTable',
            //            EntityTagValueNode::class => __DIR__ . '/Fixture/DoctrineEntity',
            //            TableTagValueNode::class => __DIR__ . '/Fixture/DoctrineTable',
            //            CustomIdGeneratorTagValueNode::class => __DIR__ . '/Fixture/DoctrineCustomIdGenerator',
            //            GeneratedValueTagValueNode::class => __DIR__ . '/Fixture/DoctrineGeneratedValue',
            //            EmbeddedTagValueNode::class => __DIR__ . '/Fixture/DoctrineEmbedded',
            //
            //            // special case
            //            SensioTemplateTagValueNode::class => __DIR__ . '/Fixture/SensioTemplate',
            //            SensioMethodTagValueNode::class => __DIR__ . '/Fixture/SensioMethod',
            //
            //            GenericTagValueNode::class => __DIR__ . '/Fixture/ConstantReference',
            //            TemplateTagValueNode::class => __DIR__ . '/Fixture/Native/Template',
            //            VarTagValueNode::class => __DIR__ . '/Fixture/Native/VarTag',
        ];
>>>>>>> 4100b04a63... Refactor doctrine/annotation parser to static reflection with phpdoc-parser
    }
}
