<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\SecurityBundle\Doctrine\ORM\Filter;

use Doctrine\ORM\Query\Filter\SQLFilter;
use Doctrine\ORM\Mapping\ClassMetadata;
use Sonatra\Bundle\SecurityBundle\Acl\Domain\AclRuleContext;
use Sonatra\Bundle\SecurityBundle\Listener\AclDoctrineORMListener;

/**
 * Acl filter.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class AclFilter extends SQLFilter
{
    protected $listener;
    protected $em;
    protected $enabled = true;

    /**
     * {@inheritdoc}
     */
    public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias)
    {
        if (!$this->enabled) {
            return '';
        }

        $class = $targetEntity->getName();
        $rule = $this->getListener()->getAclRuleManager()->getRule('VIEW', $class);
        $definition = $this->getListener()->getAclRuleManager()->getDefinition($rule);
        $am = $this->getListener()->getAclManager();
        $arm = $this->getListener()->getAclRuleManager();
        $identities = $this->getListener()->getSecurityIdentities();
        $arc = new AclRuleContext($am, $arm, $identities);

        return $definition->addFilterConstraint($arc, $this->getEntityManager(), $targetEntity, $targetTableAlias);
    }

    /**
     * {@inheritdoc}
     */
    public function enable()
    {
        $this->enabled = true;
    }

    /**
     * {@inheritdoc}
     */
    public function disable()
    {
        $this->enabled = false;
    }

    /**
     * Get the ACL Doctrine ORM Listener.
     *
     * @return AclDoctrineORMListener
     *
     * @throws \RuntimeException
     */
    protected function getListener()
    {
        if (null === $this->listener) {
            $em = $this->getEntityManager();
            $evm = $em->getEventManager();

            foreach ($evm->getListeners() as $listeners) {
                foreach ($listeners as $listener) {
                    if ($listener instanceof AclDoctrineORMListener) {
                        $this->listener = $listener;
                        break 2;
                    }
                }
            }

            if (null === $this->listener) {
                throw new \RuntimeException('Listener "AclDoctrineORMListener" was not added to the EventManager!');
            }
        }

        return $this->listener;
    }

    /**
     * Get the entity manager in parent class.
     *
     * @return EntityManager
     */
    protected function getEntityManager()
    {
        if (null === $this->em) {
            $refl = new \ReflectionProperty('Doctrine\ORM\Query\Filter\SQLFilter', 'em');
            $refl->setAccessible(true);
            $this->em = $refl->getValue($this);
        }

        return $this->em;
    }
}
