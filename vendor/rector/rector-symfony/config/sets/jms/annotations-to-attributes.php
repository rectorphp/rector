<?php

declare (strict_types=1);
namespace RectorPrefix202409;

use Rector\Config\RectorConfig;
use Rector\Php80\Rector\Class_\AnnotationToAttributeRector;
use Rector\Php80\ValueObject\AnnotationToAttribute;
/**
 * @see https://github.com/schmittjoh/serializer/pull/1320
 * @see https://github.com/schmittjoh/serializer/pull/1332
 * @see https://github.com/schmittjoh/serializer/pull/1337
 */
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(AnnotationToAttributeRector::class, [new AnnotationToAttribute('JMS\\Serializer\\Annotation\\Accessor'), new AnnotationToAttribute('JMS\\Serializer\\Annotation\\AccessorOrder'), new AnnotationToAttribute('JMS\\Serializer\\Annotation\\AccessType'), new AnnotationToAttribute('JMS\\Serializer\\Annotation\\Discriminator'), new AnnotationToAttribute('JMS\\Serializer\\Annotation\\Exclude'), new AnnotationToAttribute('JMS\\Serializer\\Annotation\\ExclusionPolicy'), new AnnotationToAttribute('JMS\\Serializer\\Annotation\\Expose'), new AnnotationToAttribute('JMS\\Serializer\\Annotation\\Groups'), new AnnotationToAttribute('JMS\\Serializer\\Annotation\\Inline'), new AnnotationToAttribute('JMS\\Serializer\\Annotation\\MaxDepth'), new AnnotationToAttribute('JMS\\Serializer\\Annotation\\PostDeserialize'), new AnnotationToAttribute('JMS\\Serializer\\Annotation\\PostSerialize'), new AnnotationToAttribute('JMS\\Serializer\\Annotation\\PreSerialize'), new AnnotationToAttribute('JMS\\Serializer\\Annotation\\ReadOnly'), new AnnotationToAttribute('JMS\\Serializer\\Annotation\\ReadOnlyProperty'), new AnnotationToAttribute('JMS\\Serializer\\Annotation\\SerializedName'), new AnnotationToAttribute('JMS\\Serializer\\Annotation\\Since'), new AnnotationToAttribute('JMS\\Serializer\\Annotation\\SkipWhenEmpty'), new AnnotationToAttribute('JMS\\Serializer\\Annotation\\Type'), new AnnotationToAttribute('JMS\\Serializer\\Annotation\\Until'), new AnnotationToAttribute('JMS\\Serializer\\Annotation\\VirtualProperty'), new AnnotationToAttribute('JMS\\Serializer\\Annotation\\XmlAttributeMap'), new AnnotationToAttribute('JMS\\Serializer\\Annotation\\XmlAttribute'), new AnnotationToAttribute('JMS\\Serializer\\Annotation\\XmlDiscriminator'), new AnnotationToAttribute('JMS\\Serializer\\Annotation\\XmlElement'), new AnnotationToAttribute('JMS\\Serializer\\Annotation\\XmlKeyValuePairs'), new AnnotationToAttribute('JMS\\Serializer\\Annotation\\XmlList'), new AnnotationToAttribute('JMS\\Serializer\\Annotation\\XmlMap'), new AnnotationToAttribute('JMS\\Serializer\\Annotation\\XmlNamespace'), new AnnotationToAttribute('JMS\\Serializer\\Annotation\\XmlRoot'), new AnnotationToAttribute('JMS\\Serializer\\Annotation\\XmlValue')]);
};
