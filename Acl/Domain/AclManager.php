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

use Sonatra\Bundle\SecurityBundle\Acl\Model\AclManagerInterface;
use Sonatra\Bundle\SecurityBundle\Acl\Model\AclRuleManagerInterface;
use Sonatra\Bundle\SecurityBundle\Acl\Util\AclUtils;
use Symfony\Component\Security\Acl\Exception\NoAceFoundException;
use Symfony\Component\Security\Acl\Exception\AclNotFoundException;
use Symfony\Component\Security\Acl\Exception\NotAllAclsFoundException;
use Symfony\Component\Security\Acl\Model\MutableAclProviderInterface;
use Symfony\Component\Security\Acl\Model\ObjectIdentityInterface;
use Symfony\Component\Security\Acl\Model\ObjectIdentityRetrievalStrategyInterface;
use Symfony\Component\Security\Acl\Model\SecurityIdentityRetrievalStrategyInterface;
use Symfony\Component\Security\Acl\Permission\BasicPermissionMap;
use Symfony\Component\Security\Acl\Voter\FieldVote;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * ACL Manager.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class AclManager implements AclManagerInterface
{
    /**
     * @var MutableAclProviderInterface
     */
    protected $aclProvider;

    /**
     * @var SecurityIdentityRetrievalStrategyInterface
     */
    protected $sidRetrievalStrategy;

    /**
     * @var ObjectIdentityRetrievalStrategyInterface
     */
    protected $oidRetrievalStrategy;

    /**
     * @var AclRuleManagerInterface
     */
    protected $aclRuleManager;

    /**
     * Constructor.
     *
     * @param MutableAclProviderInterface                $aclProvider
     * @param SecurityIdentityRetrievalStrategyInterface $sidRetrievalStrategy
     * @param ObjectIdentityRetrievalStrategyInterface   $oidRetrievalStrategy
     * @param AclRuleManagerInterface                    $aclRuleManager
     */
    public function __construct(MutableAclProviderInterface $aclProvider,
            SecurityIdentityRetrievalStrategyInterface $sidRetrievalStrategy,
            ObjectIdentityRetrievalStrategyInterface $oidRetrievalStrategy,
            AclRuleManagerInterface $aclRuleManager)
    {
        $this->aclProvider = $aclProvider;
        $this->sidRetrievalStrategy = $sidRetrievalStrategy;
        $this->oidRetrievalStrategy = $oidRetrievalStrategy;
        $this->aclRuleManager = $aclRuleManager;
    }

    /**
     * {@inheritDoc}
     */
    public function getSecurityIdentities(TokenInterface $token = null)
    {
        if (null === $token) {
            return array();
        }

        return $this->sidRetrievalStrategy->getSecurityIdentities($token);
    }

    /**
     * {@inheritDoc}
     */
    public function getObjectIdentity($domainObject)
    {
        if ($domainObject instanceof ObjectIdentityInterface) {
            return $domainObject;
        }

        return $this->oidRetrievalStrategy->getObjectIdentity($domainObject);
    }

    /**
     * {@inheritDoc}
     */
    public function getObjectIdentities(array $domainObjects)
    {
        $oids = array();

        foreach ($domainObjects as $domainObject) {
            $oid = $this->getObjectIdentity($domainObject);

            if (null !== $oid) {
                $oids[] = $oid;
            }
        }

        return $oids;
    }

    /**
     * {@inheritDoc}
     */
    public function isGranted($sids, $domainObject, $mask)
    {
        $granted = false;
        $field = null;
        $masks = array();

        // generate mask
        if (!is_array($mask)) {
            $mask = array($mask);
        }

        foreach ($mask as $m) {
            $masks[] = AclUtils::convertToMask($m);
        }

        // get the object or class
        if ($domainObject instanceof FieldVote) {
            $field = $domainObject->getField();
            $domainObject = $domainObject->getDomainObject();
        }

        $sids = AclUtils::convertSecurityIdentities($sids);
        $oid = $this->getObjectIdentity($domainObject);
        $rule = $this->getRule($mask, $domainObject, $field);
        $definition = $this->aclRuleManager->getDefinition($rule);
        $arc = new AclRuleContext($this, $this->aclRuleManager, $sids);

        return $definition->isGranted($arc, $oid, $masks, $field);
    }

    /**
     * {@inheritDoc}
     */
    public function isFieldGranted($sids, $domainObject, $field, $mask)
    {
        // override the field in FieldVote with the new field name
        if ($domainObject instanceof FieldVote) {
            $domainObject = $domainObject->getDomainObject();
        }

        return $this->isGranted($sids,
                new FieldVote($domainObject, $field), $mask);
    }

    /**
     * {@inheritDoc}
     */
    public function preloadAcls(array $objects)
    {
        $oids = $this->getObjectIdentities($objects);

        try {
            return $this->aclProvider->findAcls($oids);

        } catch (NotAllAclsFoundException $ex) {
            return $ex->getPartialResult();

        } catch (AclNotFoundException $ex) {
            return new \SplObjectStorage();
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getRule($type, $domainObject, $field = null)
    {
        if (is_array($type)) {
            $type = $type[0];
        }

        if (is_int($type)) {
            $type = AclUtils::convertToAclName($type);
        }

        $classname = AclUtils::convertDomainObjectToClassname($domainObject);

        if ($domainObject instanceof FieldVote) {
            $field = $domainObject->getField();
        }

        return $this->aclRuleManager->getRule($type, $classname, $field);
    }

    /**
     * {@inheritDoc}
     */
    public function doIsGranted(array $sids, array $masks, ObjectIdentityInterface $oid, $field = null)
    {
        try {
            $acl = $this->aclProvider->findAcl($oid);
            $masks = $this->getAllMasks($masks, $oid);

            if (null === $field) {
                return $acl->isGranted($masks, $sids);
            }

            return $acl->isFieldGranted($field, $masks, $sids);

        } catch (AclNotFoundException $e) {
        } catch (NoAceFoundException $e) {
        }

        return false;
    }

    /**
     * Get the all masks for allow the access on greater permissions define by
     * the Symfony2 ACL Advanced Pre-Authorization Decisions Documentation.
     *
     * @param array  $masks  The masks
     * @param object $object The object
     *
     * @return array The all masks to find the access
     */
    protected function getAllMasks(array $masks, $object)
    {
        $all = array();
        $map = new BasicPermissionMap();

        foreach ($masks as $mask) {
            $mask = implode('', AclUtils::convertToAclName($mask));

            if ($map->contains($mask)) {
                $all = array_merge($all, $map->getMasks($mask, $object));
            }
        }

        return array_unique($all);
    }
}
