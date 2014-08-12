<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\SecurityBundle\Command\Acl;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Sonatra\Bundle\SecurityBundle\Acl\Util\AclUtils;

/**
 * Abstract class for action command.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
abstract class AbstractActionCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setDefinition(array(
                new InputArgument('identity-type', InputArgument::REQUIRED, 'The security identity type (role, user, group)'),
                new InputArgument('identity-name', InputArgument::REQUIRED, 'The security identity name to use for the right'),
                new InputArgument('domain-class-name', InputArgument::REQUIRED, 'The domain class name to get the right for'),
                new InputArgument('domain-field-name', InputArgument::OPTIONAL, 'The domain class field name to get the right for'),
                new InputOption('domainid', null, InputOption::VALUE_REQUIRED, 'This domain id (only for object)'),
            ))
        ;
    }

    /**
     * Get classname from an entity name formatted on the symfony way.
     *
     * @param string $entityName
     *
     * @return string The FQCN
     */
    protected function getClassname($entityName)
    {
        $entityName = str_replace('/', '\\', $entityName);

        try {
            if (false !== $pos = strpos($entityName, ':')) {
                $bundle = substr($entityName, 0, $pos);
                $entityName = substr($entityName, $pos + 1);

                $cn = get_class($this->getContainer()->get('kernel')->getBundle($bundle));
                $cn = substr($cn, 0, strrpos($cn, '\\'));

                $entityName = $cn . '\Entity\\' . $entityName;
            }

        } catch (\Exception $ex) {
        }

        $entityName = new \ReflectionClass($entityName);

        return $entityName->getName();
    }

    /**
     * Add rights on domain or domain field.
     *
     * @param OutputInterface $output     The output instance
     * @param mixed           $identity   The identifier instance
     * @param array           $rights     The list of right
     * @param string          $domainType The domain type (class or object)
     * @param mixed           $domain     The classname or object instance
     * @param string|null     $field      The field name
     * @param integer         $index      The ACE index
     * @param boolean         $granting   The ACE granting
     * @param string          $strategy   The ACE granting strategy
     */
    protected function addRights(OutputInterface $output, $identity, $rights, $domainType, $domain, $field, $index, $granting, $strategy)
    {
        $aclManipulator = $this->getContainer()->get('sonatra_security.acl.manipulator');
        $fieldMethodName = null !== $field ? 'Field' : '';
        $addMethod = sprintf('add%s%sPermission', ucfirst($domainType), $fieldMethodName);
        $getMethod = sprintf('get%s%sPermission', ucfirst($domainType), $fieldMethodName);

        if (null !== $field) {
            $aclManipulator->$addMethod($identity, $domain, $field, $rights, $index, $granting, $strategy);
        } else {
            $aclManipulator->$addMethod($identity, $domain, $rights, $index, $granting, $strategy);
        }

        // display new rights
        $mask = $aclManipulator->$getMethod($identity, $domain, $field);
        $rights = AclUtils::convertToAclName($mask);
        $fieldOutput = null !== $field ? ' field' : '';
        $msg = sprintf('<info>Newing %s%s rights:</info> [ %s ]', $domainType, $fieldOutput, implode(', ', $rights));
        $output->writeln(array('', $msg));
    }

    /**
     * Revoke rights on domain or domain field.
     *
     * @param OutputInterface $output     The output instance
     * @param mixed           $identity   The identifier instance
     * @param array           $rights     The list of right
     * @param string          $domainType The domain type (class or object)
     * @param mixed           $domain     The classname or object instance
     * @param string|null     $field      The field name
     */
    protected function revokeRights(OutputInterface $output, $identity, $rights, $domainType, $domain, $field)
    {
        $aclManipulator = $this->getContainer()->get('sonatra_security.acl.manipulator');
        $fieldMethodName = null !== $field ? 'Field' : '';
        $revokeMethod = sprintf('revoke%s%sPermission', ucfirst($domainType), $fieldMethodName);
        $deleteMethod = sprintf('delete%s%sPermissions', ucfirst($domainType), $fieldMethodName);
        $getMethod = sprintf('get%s%sPermission', ucfirst($domainType), $fieldMethodName);

        if (empty($rights)) {
            $aclManipulator->$deleteMethod($identity, $domain, $field);

        } elseif (null !== $field) {
            $aclManipulator->$revokeMethod($identity, $domain, $field, $rights);

        } else {
            $aclManipulator->$revokeMethod($identity, $domain, $rights);
        }

        // display new rights
        $mask = $aclManipulator->$getMethod($identity, $domain, $field);
        $rights = AclUtils::convertToAclName($mask);
        $fieldOutput = null !== $field ? ' field' : '';
        $msg = sprintf('<info>Remaining %s%s rights:</info> [ %s ]', $domainType, $fieldOutput, implode(', ', $rights));
        $output->writeln(array('', $msg));
    }
}
