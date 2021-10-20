<?php

/*
 * This file is part of composer/semver.
 *
 * (c) Composer <https://github.com/composer>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */
namespace RectorPrefix20211020\Composer\Semver\Constraint;

/**
 * Blackhole of constraints, nothing escapes it
 */
class MatchNoneConstraint implements \RectorPrefix20211020\Composer\Semver\Constraint\ConstraintInterface
{
    /** @var string|null */
    protected $prettyString;
    /**
     * @param ConstraintInterface $provider
     *
     * @return bool
     */
    public function matches($provider)
    {
        return \false;
    }
    /**
     * {@inheritDoc}
     */
    public function compile($otherOperator)
    {
        return 'false';
    }
    /**
     * {@inheritDoc}
     */
    public function setPrettyString($prettyString)
    {
        $this->prettyString = $prettyString;
    }
    /**
     * {@inheritDoc}
     */
    public function getPrettyString()
    {
        if ($this->prettyString) {
            return $this->prettyString;
        }
        return (string) $this;
    }
    /**
     * {@inheritDoc}
     */
    public function __toString()
    {
        return '[]';
    }
    /**
     * {@inheritDoc}
     */
    public function getUpperBound()
    {
        return new \RectorPrefix20211020\Composer\Semver\Constraint\Bound('0.0.0.0-dev', \false);
    }
    /**
     * {@inheritDoc}
     */
    public function getLowerBound()
    {
        return new \RectorPrefix20211020\Composer\Semver\Constraint\Bound('0.0.0.0-dev', \false);
    }
}
