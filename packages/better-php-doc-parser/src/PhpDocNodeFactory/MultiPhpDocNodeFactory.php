<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNodeFactory;

use PhpParser\Node;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use Rector\BetterPhpDocParser\Contract\GenericPhpDocNodeFactoryInterface;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Class_\EmbeddableTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Class_\EntityTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Class_\InheritanceTypeTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Property_\ColumnTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Property_\CustomIdGeneratorTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Property_\GeneratedValueTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Property_\IdTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Property_\JoinColumnTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Gedmo\BlameableTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Gedmo\LocaleTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Gedmo\LoggableTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Gedmo\SlugTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Gedmo\SoftDeleteableTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Gedmo\TranslatableTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Gedmo\TreeLeftTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Gedmo\TreeLevelTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Gedmo\TreeParentTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Gedmo\TreeRightTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Gedmo\TreeRootTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Gedmo\TreeTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Gedmo\VersionedTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\JMS\JMSInjectParamsTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\JMS\JMSServiceValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\JMS\SerializerTypeTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\PHPDI\PHPDIInjectTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Sensio\SensioMethodTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Sensio\SensioRouteTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Sensio\SensioTemplateTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Symfony\SymfonyRouteTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Symfony\Validator\Constraints\AssertChoiceTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Symfony\Validator\Constraints\AssertEmailTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Symfony\Validator\Constraints\AssertRangeTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Symfony\Validator\Constraints\AssertTypeTagValueNode;

final class MultiPhpDocNodeFactory extends AbstractPhpDocNodeFactory implements GenericPhpDocNodeFactoryInterface
{
    /**
     * @return array<string, string>
     */
    public function getTagValueNodeClassesToAnnotationClasses(): array
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
        $tagValueNodeClassesToAnnotationClasses = $this->getTagValueNodeClassesToAnnotationClasses();
        $tagValueNodeClass = array_search($annotationClass, $tagValueNodeClassesToAnnotationClasses, true);

        $annotation = $this->nodeAnnotationReader->readAnnotation($node, $annotationClass);
        if ($annotation === null) {
            return null;
        }

        $items = $this->annotationItemsResolver->resolve($annotation);
        $content = $this->annotationContentResolver->resolveFromTokenIterator($tokenIterator);

        return new $tagValueNodeClass($items, $content);
    }
}
