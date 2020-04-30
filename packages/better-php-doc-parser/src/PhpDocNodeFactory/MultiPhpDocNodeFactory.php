<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNodeFactory;

use DI\Annotation\Inject;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\CustomIdGenerator;
use Doctrine\ORM\Mapping\Embeddable;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\InheritanceType;
use Doctrine\ORM\Mapping\JoinColumn;
use Gedmo\Mapping\Annotation\Blameable;
use Gedmo\Mapping\Annotation\Locale;
use Gedmo\Mapping\Annotation\Loggable;
use Gedmo\Mapping\Annotation\Slug;
use Gedmo\Mapping\Annotation\SoftDeleteable;
use Gedmo\Mapping\Annotation\Translatable;
use Gedmo\Mapping\Annotation\Tree;
use Gedmo\Mapping\Annotation\TreeLeft;
use Gedmo\Mapping\Annotation\TreeLevel;
use Gedmo\Mapping\Annotation\TreeParent;
use Gedmo\Mapping\Annotation\TreeRight;
use Gedmo\Mapping\Annotation\TreeRoot;
use Gedmo\Mapping\Annotation\Versioned;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;
use JMS\Serializer\Annotation\Type;
use PhpParser\Node;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Class_\EmbeddableTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Class_\EntityTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Class_\InheritanceTypeTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Property_\ColumnTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Property_\CustomIdGeneratorTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Property_\GeneratedValueTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Property_\IdTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Property_\JoinColumnTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNode\Gedmo\BlameableTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNode\Gedmo\LocaleTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNode\Gedmo\LoggableTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNode\Gedmo\SlugTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNode\Gedmo\SoftDeleteableTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNode\Gedmo\TranslatableTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNode\Gedmo\TreeLeftTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNode\Gedmo\TreeLevelTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNode\Gedmo\TreeParentTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNode\Gedmo\TreeRightTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNode\Gedmo\TreeRootTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNode\Gedmo\TreeTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNode\Gedmo\VersionedTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNode\JMS\JMSInjectParamsTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNode\JMS\JMSServiceValueNode;
use Rector\BetterPhpDocParser\PhpDocNode\JMS\SerializerTypeTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNode\PHPDI\PHPDIInjectTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNode\Sensio\SensioMethodTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNode\Sensio\SensioTemplateTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNode\Symfony\SymfonyRouteTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNode\Symfony\Validator\Constraints\AssertChoiceTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNode\Symfony\Validator\Constraints\AssertEmailTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNode\Symfony\Validator\Constraints\AssertRangeTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNode\Symfony\Validator\Constraints\AssertTypeTagValueNode;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Type as SymfonyValidationType;

final class MultiPhpDocNodeFactory extends AbstractPhpDocNodeFactory
{
    /**
     * @return string[]
     */
    public function getClasses(): array
    {
        return [
            // tag value node class => annotation class

            // doctrine
            EmbeddableTagValueNode::class => Embeddable::class,
            EntityTagValueNode::class => Entity::class,
            InheritanceTypeTagValueNode::class => InheritanceType::class,
            ColumnTagValueNode::class => Column::class,
            CustomIdGeneratorTagValueNode::class => CustomIdGenerator::class,
            IdTagValueNode::class => Id::class,
            GeneratedValueTagValueNode::class => GeneratedValue::class,
            JoinColumnTagValueNode::class => JoinColumn::class,

            // symfony/http-kernel
            SymfonyRouteTagValueNode::class => Route::class,

            // symfony/validator
            AssertRangeTagValueNode::class => Range::class,
            AssertTypeTagValueNode::class => SymfonyValidationType::class,
            AssertChoiceTagValueNode::class => Choice::class,
            AssertEmailTagValueNode::class => Email::class,

            // gedmo
            LocaleTagValueNode::class => Locale::class,
            BlameableTagValueNode::class => Blameable::class,
            SlugTagValueNode::class => Slug::class,
            SoftDeleteableTagValueNode::class => SoftDeleteable::class,
            TreeRootTagValueNode::class => TreeRoot::class,
            TreeLeftTagValueNode::class => TreeLeft::class,
            TreeLevelTagValueNode::class => TreeLevel::class,
            TreeParentTagValueNode::class => TreeParent::class,
            TreeRightTagValueNode::class => TreeRight::class,
            VersionedTagValueNode::class => Versioned::class,
            TranslatableTagValueNode::class => Translatable::class,
            LoggableTagValueNode::class => Loggable::class,
            TreeTagValueNode::class => Tree::class,

            // Sensio
            SensioTemplateTagValueNode::class => Template::class,
            SensioMethodTagValueNode::class => Method::class,

            // JMS
            JMSInjectParamsTagValueNode::class => InjectParams::class,
            JMSServiceValueNode::class => Service::class,
            SerializerTypeTagValueNode::class => Type::class,

            PHPDIInjectTagValueNode::class => Inject::class,
        ];
    }

    public function createFromNodeAndTokens(
        Node $node,
        TokenIterator $tokenIterator,
        string $annotationClass
    ): ?PhpDocTagValueNode {
        $tagValueNodeClassToAnnotationClass = $this->getClasses();
        $tagValueNodeClass = array_search($annotationClass, $tagValueNodeClassToAnnotationClass, true);

        $annotation = $this->nodeAnnotationReader->readAnnotation($node, $annotationClass);
        if ($annotation === null) {
            return null;
        }

        $annotationContent = $this->annotationContentResolver->resolveFromTokenIterator($tokenIterator);
        return new $tagValueNodeClass($annotation, $annotationContent);
    }
}
