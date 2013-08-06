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
use Sonatra\Bundle\SecurityBundle\Doctrine\ORM\Listener\AclListener;

/**
 * Acl filter.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class AclFilter extends SQLFilter
{
    protected $listener;
    protected $em;

    /**
     * {@inheritdoc}
     */
    public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias)
    {
        $am = $this->getListener()->getAclManager();
        $arm = $this->getListener()->getAclRuleManager();
        $class = $targetEntity->getName();
        $rule = $arm->getRule('VIEW', $class);
        $definition = $arm->getDefinition($rule);
        $identities = $this->getListener()->getSecurityIdentities();
        $arc = new AclRuleContext($am, $arm, $identities);

        return $definition->addFilterConstraint($arc, $this->getEntityManager(), $targetEntity, $targetTableAlias);
    }

    /**
     * Get the ACL Doctrine ORM Listener.
     *
     * @return AclListener
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
                    if ($listener instanceof AclListener) {
                        $this->listener = $listener;
                        break 2;
                    }
                }
            }

            if (null === $this->listener) {
                throw new \RuntimeException('Listener "AclListener" was not added to the EventManager!');
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
