<?php

declare(strict_types=1);

namespace Sensio\Bundle\FrameworkExtraBundle\Configuration;

if (class_exists('Sensio\Bundle\FrameworkExtraBundle\Configuration\Template')) {
    return;
}

// mimics https://github.com/sensiolabs/SensioFrameworkExtraBundle/blob/master/Configuration/Template.php, is missing localy

/**
 * The Template class handles the Template annotation parts.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @Annotation
 */
class Template extends ConfigurationAnnotation
{
    /**
     * The template.
     *
     * @var string
     */
    public $template;

    /**
     * The associative array of template variables.
     *
     * @var array
     */
    public $vars = [];

    /**
     * Should the template be streamed?
     *
     * @var bool
     */
    public $streamable = false;

    /**
     * The controller (+action) this annotation is set to.
     *
     * @var array
     */
    public $owner = [];

    /**
     * Returns the array of templates variables.
     *
     * @return array
     */
    public function getVars()
    {
        return $this->vars;
    }

    /**
     * @param bool $streamable
     */
    public function setIsStreamable($streamable)
    {
        $this->streamable = $streamable;
    }

    /**
     * @return bool
     */
    public function isStreamable()
    {
        return (bool) $this->streamable;
    }

    /**
     * Sets the template variables.
     *
     * @param array $vars The template variables
     */
    public function setVars($vars)
    {
        $this->vars = $vars;
    }

    /**
     * Sets the template logic name.
     *
     * @param string $template The template logic name
     */
    public function setValue($template)
    {
        $this->setTemplate($template);
    }

    /**
     * Returns the template.
     *
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * Sets the template.
     *
     * @param string $template The template
     */
    public function setTemplate($template)
    {
        $this->template = $template;
    }

    /**
     * Returns the annotation alias name.
     *
     * @return string
     *
     * @see ConfigurationInterface
     */
    public function getAliasName()
    {
        return 'template';
    }

    /**
     * Only one template directive is allowed.
     *
     * @return bool
     *
     * @see ConfigurationInterface
     */
    public function allowArray()
    {
        return false;
    }

    public function setOwner(array $owner)
    {
        $this->owner = $owner;
    }

    /**
     * The controller (+action) this annotation is attached to.
     *
     * @return array
     */
    public function getOwner()
    {
        return $this->owner;
    }
}
