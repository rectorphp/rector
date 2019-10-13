<?php

declare(strict_types=1);

namespace Sensio\Bundle\FrameworkExtraBundle\Configuration;

use RuntimeException;

if (class_exists('Sensio\Bundle\FrameworkExtraBundle\Configuration\Template')) {
    return;
}

// mimics https://github.com/sensiolabs/SensioFrameworkExtraBundle/blob/master/Configuration/Template.php, is missing localy

/**
 * @Annotation
 */
class Template
{
    /**
     * The template.
     *
     * @var string
     */
    protected $template;

    /**
     * The associative array of template variables.
     *
     * @var array
     */
    private $vars = [];

    /**
     * Should the template be streamed?
     *
     * @var bool
     */
    private $streamable = false;

    /**
     * The controller (+action) this annotation is set to.
     *
     * @var array
     */
    private $owner = [];

    public function __construct(array $values)
    {
        foreach ($values as $k => $v) {
            if (! method_exists($this, $name = 'set' . $k)) {
                throw new RuntimeException(sprintf('Unknown key "%s" for annotation "@%s".', $k, static::class));
            }
            $this->{$name}($v);
        }
    }

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
    public function setIsStreamable($streamable): void
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
    public function setVars($vars): void
    {
        $this->vars = $vars;
    }

    /**
     * Sets the template logic name.
     *
     * @param string $template The template logic name
     */
    public function setValue($template): void
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
    public function setTemplate($template): void
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

    /**
     * @param array $owner
     */
    public function setOwner(array $owner): void
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
