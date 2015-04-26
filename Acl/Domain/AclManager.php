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

use Sonatra\Bundle\SecurityBundle\Acl\Model\MutableAclProviderInterface;
use Sonatra\Bundle\SecurityBundle\Acl\Model\AclManagerInterface;
use Sonatra\Bundle\SecurityBundle\Acl\Model\AclRuleManagerInterface;
use Sonatra\Bundle\SecurityBundle\Acl\Model\RuleDefinitionInterface;
use Sonatra\Bundle\SecurityBundle\Acl\Util\AclUtils;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Exception\NoAceFoundException;
use Symfony\Component\Security\Acl\Exception\AclNotFoundException;
use Symfony\Component\Security\Acl\Exception\NotAllAclsFoundException;
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
     * @var array
     */
    protected $cacheObjectRules;

    /**
     * @var array
     */
    protected $cachePreloadTypes;

    /**
     * @var array
     */
    protected $cacheCreatedClassOids;

    /**
     * @var array
     */
    protected $excludedOids;

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
        $this->cacheObjectRules = array();
        $this->cachePreloadTypes = array();
        $this->cacheCreatedClassOids = array();
        $this->excludedOids = array();
    }

    /**
     * {@inheritdoc}
     */
    public function isDisabled()
    {
        return $this->aclRuleManager->isDisabled();
    }

    /**
     * {@inheritdoc}
     */
    public function enable()
    {
        $this->aclRuleManager->enable();

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function disable()
    {
        $this->aclRuleManager->disable();

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSecurityIdentities(TokenInterface $token = null)
    {
        if (null === $token) {
            return array();
        }

        return $this->sidRetrievalStrategy->getSecurityIdentities($token);
    }

    /**
     * {@inheritdoc}
     */
    public function getObjectIdentity($domainObject)
    {
        if ($domainObject instanceof ObjectIdentityInterface) {
            return $domainObject;
        }

        return $this->oidRetrievalStrategy->getObjectIdentity($domainObject);
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function createClassObjectIdentity($type)
    {
        $id = $type.'__class';

        if (!isset($this->cacheCreatedClassOids[$id])) {
            $this->cacheCreatedClassOids[$id] = new ObjectIdentity('class', $type);
        }

        return $this->cacheCreatedClassOids[$id];
    }

    /**
     * {@inheritdoc}
     */
    public function getPreloadTypes($domainObject)
    {
        $classname = AclUtils::convertDomainObjectToClassname($domainObject);

        if (!isset($this->cachePreloadTypes[$classname])) {
            $rules = $this->getObjectRules($classname);
            $preloadTypes = array();

            // find types in class
            foreach ($rules['class'] as $ruleName) {
                $rule = $this->aclRuleManager->getDefinition($ruleName);
                $preloadTypes = array_merge($preloadTypes, $rule->getTypes());
            }

            // find types in class fields
            if (!in_array(RuleDefinitionInterface::TYPE_CLASS, $preloadTypes)
                    || !in_array(RuleDefinitionInterface::TYPE_OBJECT, $preloadTypes)) {
                foreach ($rules['fields'] as $fieldRules) {
                    foreach ($fieldRules as $ruleName) {
                        $rule = $this->aclRuleManager->getDefinition($ruleName);
                        $preloadTypes = array_merge($preloadTypes, $rule->getTypes());
                    }
                }
            }

            $this->cachePreloadTypes[$classname] = array_unique($preloadTypes);
        }

        return $this->cachePreloadTypes[$classname];
    }

    /**
     * {@inheritdoc}
     */
    public function isGranted($sids, $domainObject, $mask)
    {
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
        $definition->setAclRuleManager($this->aclRuleManager);
        $rcd = new RuleContextDefinition($sids, $oid, $masks, $field);

        return $definition->isGranted($rcd);
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function preloadAcls(array $objects)
    {
        $oids = array();
        $tmpAddClassOids = array();

        foreach ($this->getObjectIdentities($objects) as $oid) {
            $classname = $oid->getType();
            $id = $classname.'__'.$oid->getIdentifier();

            if (in_array($id, $this->excludedOids)) {
                continue;
            }

            $preloadTypes = $this->getPreloadTypes($classname);

            // add class object identifier
            if (in_array(RuleDefinitionInterface::TYPE_CLASS, $preloadTypes)) {
                if ('class' === $oid->getIdentifier()) {
                    $oids[$id] = $oid;
                } else {
                    $tmpAddClassOids[] = $classname;
                }
            }

            // add object identifier
            if ((in_array(RuleDefinitionInterface::TYPE_OBJECT, $preloadTypes) && 'class' !== $oid->getIdentifier())
                    || in_array(RuleDefinitionInterface::TYPE_SKIP_OPTIMIZATION, $preloadTypes)) {
                $oids[$id] = $oid;
            }
        }

        foreach ($tmpAddClassOids as $classname) {
            $oids[$classname.'__class'] = $this->createClassObjectIdentity($classname);
        }

        try {
            $result = $this->aclProvider->findAcls(array_values($oids));
        } catch (NotAllAclsFoundException $ex) {
            $result = $ex->getPartialResult();
        } catch (AclNotFoundException $ex) {
            $result = new \SplObjectStorage();
        }

        $this->excludeNonexistentAcls($result, $oids);

        return $result;
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function getRules($domainObject, $field = null, array $types = array())
    {
        $rules = array();

        if (empty($types)) {
            $types = array(
                    BasicPermissionMap::PERMISSION_VIEW,
                    BasicPermissionMap::PERMISSION_EDIT,
                    BasicPermissionMap::PERMISSION_CREATE,
                    BasicPermissionMap::PERMISSION_DELETE,
                    BasicPermissionMap::PERMISSION_UNDELETE,
                    BasicPermissionMap::PERMISSION_OPERATOR,
                    BasicPermissionMap::PERMISSION_MASTER,
                    BasicPermissionMap::PERMISSION_OWNER,
            );
        }

        foreach ($types as $type) {
            $rules[$type] = $this->getRule($type, $domainObject, $field);
        }

        return $rules;
    }

    /**
     * {@inheritdoc}
     */
    public function getObjectRules($domainObject)
    {
        $domainObject = AclUtils::convertDomainObjectToClassname($domainObject);

        if (isset($this->cacheObjectRules[$domainObject])) {
            return $this->cacheObjectRules[$domainObject];
        }

        $rules = array();
        $rules['class'] = $this->getRules($domainObject);
        $rules['fields'] = array();
        $ref = new \ReflectionClass($domainObject);

        foreach ($ref->getProperties() as $property) {
            $name = $property->getName();
            $rules['fields'][$name] = $this->getRules($domainObject, $name);
        }

        $this->cacheObjectRules[$domainObject] = $rules;

        return $rules;
    }

    /**
     * {@inheritdoc}
     */
    public function doIsGranted(array $sids, array $masks,
            ObjectIdentityInterface $oid, ObjectIdentityInterface $initOid,
            $field = null)
    {
        $oids = array($oid);

        if (!$initOid->equals($oid)) {
            $oids[] = $initOid;
        }

        try {
            $acl = $this->preloadAcls($oids)->offsetGet($oid);
            $masks = $this->getAllMasks($masks, $oid);

            if (null === $field) {
                return $acl->isGranted($masks, $sids);
            }

            return $acl->isFieldGranted($field, $masks, $sids);
        } catch (\UnexpectedValueException $e) {
        } catch (NoAceFoundException $e) {
        }

        return false;
    }

    /**
     * Get the all masks for allow the access on greater permissions define by
     * the Symfony2 ACL Advanced Pre-Authorization Decisions Documentation.
     *
     * @param array                   $masks  The masks
     * @param ObjectIdentityInterface $object The object
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

    /**
     * Exclude nonexistent Acls for next search.
     *
     * @param \SplObjectStorage         $result
     * @param ObjectIdentityInterface[] $oids
     */
    protected function excludeNonexistentAcls(\SplObjectStorage $result, array $oids)
    {
        /* @var ObjectIdentityInterface $oid */
        foreach ($result as $oid) {
            $id = $oid->getType().'__'.$oid->getIdentifier();

            if (array_key_exists($id, $oids)) {
                unset($oids[$id]);
            }
        }

        foreach ($oids as $id => $oid) {
            if ($this->aclProvider->hasLoadedAcls($oid)) {
                if (array_key_exists($id, $oids)) {
                    unset($oids[$id]);
                }
            }
        }

        if (count($oids) > 0) {
            $this->excludedOids = array_merge($this->excludedOids, array_keys($oids));
            $this->excludedOids = array_unique($this->excludedOids);
        }
    }
}
