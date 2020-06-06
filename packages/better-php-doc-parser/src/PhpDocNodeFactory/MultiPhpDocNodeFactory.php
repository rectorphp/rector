<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNodeFactory;

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
use Rector\BetterPhpDocParser\PhpDocNode\Sensio\SensioRouteTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNode\Sensio\SensioTemplateTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNode\Symfony\SymfonyRouteTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNode\Symfony\Validator\Constraints\AssertChoiceTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNode\Symfony\Validator\Constraints\AssertEmailTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNode\Symfony\Validator\Constraints\AssertRangeTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNode\Symfony\Validator\Constraints\AssertTypeTagValueNode;

final class MultiPhpDocNodeFactory extends AbstractPhpDocNodeFactory
{
    /**
     * @return string[]
     */
    public function getClasses(): array
    {
        return [
            // tag value node class => annotation class

            // doctrine - intentionally in string, so prefixer of rector.phar doesn't prefix it
            EmbeddableTagValueNode::class => 'Doctrine\ORM\Mapping\Embeddable',
            EntityTagValueNode::class => 'Doctrine\ORM\Mapping\Entity',
            InheritanceTypeTagValueNode::class => 'Doctrine\ORM\Mapping\InheritanceType',
            ColumnTagValueNode::class => 'Doctrine\ORM\Mapping\Column',
            CustomIdGeneratorTagValueNode::class => 'Doctrine\ORM\Mapping\CustomIdGenerator',
            IdTagValueNode::class => 'Doctrine\ORM\Mapping\Id',
            GeneratedValueTagValueNode::class => 'Doctrine\ORM\Mapping\GeneratedValue',
            JoinColumnTagValueNode::class => 'Doctrine\ORM\Mapping\JoinColumn',

            // symfony/http-kernel
            SymfonyRouteTagValueNode::class => 'Symfony\Component\Routing\Annotation\Route',

            // symfony/validator
            AssertRangeTagValueNode::class => 'Symfony\Component\Validator\Constraints\Range',
            AssertTypeTagValueNode::class => 'Symfony\Component\Validator\Constraints\Type',
            AssertChoiceTagValueNode::class => 'Symfony\Component\Validator\Constraints\Choice',
            AssertEmailTagValueNode::class => 'Symfony\Component\Validator\Constraints\Email',

            // gedmo
            LocaleTagValueNode::class => 'Gedmo\Mapping\Annotation\Locale',
            BlameableTagValueNode::class => 'Gedmo\Mapping\Annotation\Blameable',
            SlugTagValueNode::class => 'Gedmo\Mapping\Annotation\Slug',
            SoftDeleteableTagValueNode::class => 'Gedmo\Mapping\Annotation\SoftDeleteable',
            TreeRootTagValueNode::class => 'Gedmo\Mapping\Annotation\TreeRoot',
            TreeLeftTagValueNode::class => 'Gedmo\Mapping\Annotation\TreeLeft',
            TreeLevelTagValueNode::class => 'Gedmo\Mapping\Annotation\TreeLevel',
            TreeParentTagValueNode::class => 'Gedmo\Mapping\Annotation\TreeParent',
            TreeRightTagValueNode::class => 'Gedmo\Mapping\Annotation\TreeRight',
            VersionedTagValueNode::class => 'Gedmo\Mapping\Annotation\Versioned',
            TranslatableTagValueNode::class => 'Gedmo\Mapping\Annotation\Translatable',
            LoggableTagValueNode::class => 'Gedmo\Mapping\Annotation\Loggable',
            TreeTagValueNode::class => 'Gedmo\Mapping\Annotation\Tree',

            // Sensio
            SensioTemplateTagValueNode::class => 'Sensio\Bundle\FrameworkExtraBundle\Configuration\Template',
            SensioMethodTagValueNode::class => 'Sensio\Bundle\FrameworkExtraBundle\Configuration\Method',
            SensioRouteTagValueNode::class => 'Sensio\Bundle\FrameworkExtraBundle\Configuration\Route',

            // JMS
            JMSInjectParamsTagValueNode::class => 'JMS\DiExtraBundle\Annotation\InjectParams',
            JMSServiceValueNode::class => 'JMS\DiExtraBundle\Annotation\Service',
            SerializerTypeTagValueNode::class => 'JMS\Serializer\Annotation\Type',

            PHPDIInjectTagValueNode::class => 'DI\Annotation\Inject',
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

        $items = $this->annotationItemsResolver->resolve($annotation);
        $content = $this->annotationContentResolver->resolveFromTokenIterator($tokenIterator);

        return new $tagValueNodeClass($items, $content);
    }
}
