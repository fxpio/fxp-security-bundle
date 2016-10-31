<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\SecurityBundle\Acl\Domain;

use Sonatra\Bundle\SecurityBundle\Acl\Model\RuleContextDefinitionInterface;
use Symfony\Component\Security\Acl\Model\ObjectIdentityInterface;
use Symfony\Component\Security\Acl\Model\SecurityIdentityInterface;

/**
 * Class for Acl Rule Context Definition.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class RuleContextDefinition extends AbstractRuleContext implements RuleContextDefinitionInterface
{
    /**
     * @var ObjectIdentityInterface
     */
    protected $oid;

    /**
     * @var array
     */
    protected $masks;

    /**
     * @var string|null
     */
    protected $field;

    /**
     * @var object|null
     */
    protected $object;

    /**
     * Constructor.
     *
     * @param SecurityIdentityInterface[] $sids   The security identities
     * @param ObjectIdentityInterface     $oid    The object identity
     * @param array                       $masks  The masks
     * @param string|null                 $field  The object field
     * @param object|null                 $object The object instance
     */
    public function __construct(array $sids,
                                ObjectIdentityInterface $oid,
                                array $masks,
                                $field = null,
                                $object = null)
    {
        parent::__construct($sids);

        $this->oid = $oid;
        $this->masks = $masks;
        $this->field = $field;
        $this->object = $object;
    }

    /**
     * {@inheritdoc}
     */
    public function getObjectIdentity()
    {
        return $this->oid;
    }

    /**
     * {@inheritdoc}
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * {@inheritdoc}
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * {@inheritdoc}
     */
    public function getMasks()
    {
        return $this->masks;
    }
}
