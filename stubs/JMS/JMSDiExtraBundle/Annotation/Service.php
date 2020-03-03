<?php

declare(strict_types=1);

namespace JMS\DiExtraBundle\Annotation;

// mimics @see https://github.com/schmittjoh/JMSDiExtraBundle/blob/master/Annotation/Service.php

if (class_exists('JMS\DiExtraBundle\Annotation\Service')) {
    return;
}

/**
 * @Annotation
 * @Target({"CLASS", "METHOD"})
 */
class Service extends Reference
{
    /** @var string */
    public $id;

    /** @var string */
    public $parent;

    /** @var bool */
    public $public;

    /** @var string */
    public $scope;

    /** @var bool */
    public $shared;

    /** @var string */
    public $deprecated;

    /** @var string */
    public $decorates;

    /**
     * @var string
     *
     * @deprecated since version 1.8, to be removed in 2.0. Use $decorationInnerName instead.
     */
    public $decoration_inner_name;

    /** @var string */
    public $decorationInnerName;

    /** @var bool */
    public $abstract;

    /** @var array<string> */
    public $environments = array();

    /** @var bool */
    public $autowire;

    /** @var array<string> */
    public $autowiringTypes;
}
