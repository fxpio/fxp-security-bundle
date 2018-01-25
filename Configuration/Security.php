<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Bundle\SecurityBundle\Configuration;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ConfigurationAnnotation;

/**
 * The Security class handles the Security annotation.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 *
 * @Annotation
 */
class Security extends ConfigurationAnnotation
{
    /**
     * @var string
     */
    protected $expression;

    /**
     * @var bool
     */
    protected $override = false;

    /**
     * Get the expression.
     *
     * @return string
     */
    public function getExpression()
    {
        return $this->expression;
    }

    /**
     * Set the expression.
     *
     * @param string $expression The expression
     */
    public function setExpression($expression)
    {
        $this->expression = $expression;
    }

    /**
     * Set the expression.
     *
     * @param string $expression The expression
     */
    public function setValue($expression)
    {
        $this->setExpression($expression);
    }

    /**
     * Define if the annotation override all previous security annotation.
     *
     * @param bool $override
     */
    public function setOverride($override)
    {
        $this->override = (bool) $override;
    }

    /**
     * Check if the annotation override all previous security annotation.
     *
     * @return bool
     */
    public function isOverriding()
    {
        return $this->override;
    }

    /**
     * {@inheritdoc}
     */
    public function getAliasName()
    {
        return 'fxp_security';
    }

    /**
     * {@inheritdoc}
     */
    public function allowArray()
    {
        return true;
    }
}
