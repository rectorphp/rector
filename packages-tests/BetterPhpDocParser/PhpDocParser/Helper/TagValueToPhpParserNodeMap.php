<?php

declare(strict_types=1);

namespace Rector\Tests\BetterPhpDocParser\PhpDocParser\Helper;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
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

final class TagValueToPhpParserNodeMap
{
    /**
     * @var array<class-string<PhpDocTagValueNode>, class-string<Node>>
     */
    public const MAP = [
        SymfonyRouteTagValueNode::class => ClassMethod::class,
        SlugTagValueNode::class => Property::class,

        // new approach
        'Gedmo\Mapping\Annotation\Blameable' => Property::class,

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
