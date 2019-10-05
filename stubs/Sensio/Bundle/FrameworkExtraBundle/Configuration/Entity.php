<?php declare(strict_types=1);

namespace Sensio\Bundle\FrameworkExtraBundle\Configuration;

if (class_exists('Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity')) {
    return;
}

/**
 * @Annotation
 */
class Entity extends ParamConverter
{
    public function setExpr($expr)
    {
        $options = $this->getOptions();
        $options['expr'] = $expr;
        $this->setOptions($options);
    }
}
