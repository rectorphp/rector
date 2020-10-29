<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Tests\PhpDocParser\Helper;

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\TemplateTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Class_\EmbeddedTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Class_\EntityTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Class_\TableTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Property_\ColumnTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Property_\CustomIdGeneratorTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Property_\GeneratedValueTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Property_\JoinTableTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Gedmo\BlameableTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Gedmo\SlugTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Sensio\SensioMethodTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Sensio\SensioTemplateTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Symfony\SymfonyRouteTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Symfony\Validator\Constraints\AssertChoiceTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Symfony\Validator\Constraints\AssertTypeTagValueNode;

final class TagValueToPhpParserNodeMap
{
    /**
     * @var string[]
     */
    public const MAP = [
        SymfonyRouteTagValueNode::class => ClassMethod::class,
        SlugTagValueNode::class => Property::class,
        BlameableTagValueNode::class => Property::class,
        // symfony/validation
        AssertChoiceTagValueNode::class => Property::class,
        AssertTypeTagValueNode::class => Property::class,
        // doctrine
        ColumnTagValueNode::class => Property::class,
        JoinTableTagValueNode::class => Property::class,
        EntityTagValueNode::class => Class_::class,
        TableTagValueNode::class => Class_::class,
        CustomIdGeneratorTagValueNode::class => Property::class,
        GeneratedValueTagValueNode::class => Property::class,
        EmbeddedTagValueNode::class => Property::class,
        // special case for constants
        GenericTagValueNode::class => Property::class,
        SensioTemplateTagValueNode::class => Class_::class,
        SensioMethodTagValueNode::class => ClassMethod::class,
        TemplateTagValueNode::class => Class_::class,
        VarTagValueNode::class => Property::class,
    ];
}
