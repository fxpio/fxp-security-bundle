<?php

/**
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
     * @var string
     */
    protected $field;

    /**
     * Constructor.
     *
     * @param array                   $sids
     * @param ObjectIdentityInterface $oid
     * @param array                   $masks
     * @param string                  $field
     */
    public function __construct(array $sids, ObjectIdentityInterface $oid, array $masks, $field = null)
    {
        parent::__construct($sids);

        $this->oid = $oid;
        $this->masks = $masks;
        $this->field = $field;
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
