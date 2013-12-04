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
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Sonatra\Bundle\SecurityBundle\Acl\Util\AclUtils;
use Sonatra\Bundle\SecurityBundle\Exception\InvalidArgumentException;

/**
 * Remove entry (class or object) rights.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class RemoveCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('security:acl:remove')
        ->setDescription('Remove a specified right from a given identifier on a given domain (class or object).')
        ->setDefinition(array(
                new InputArgument('identity-type', InputArgument::REQUIRED, 'The security identity type (role, user, group)'),
                new InputArgument('identity-name', InputArgument::REQUIRED, 'The security identity name to use for the right'),
                new InputArgument('domain-class-name', InputArgument::REQUIRED, 'The domain class name to get the right for'),
                new InputArgument('domain-field-name', InputArgument::OPTIONAL, 'The domain class field name to get the right for'),
                new InputOption('domainid', null, InputOption::VALUE_REQUIRED, 'This domain id (only for object)'),
                new InputOption('right', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                        'Specifies the right(s) to set on the given class for the given security identity')
        ))
        ->setHelp(<<<EOF
The <info>acl:right:revoke</info> command revokes the given rights for the
given security identity on a specified domain (class or object).

If the "right" option isn't specified, then all the available rights will be
revoke on the domain for the security identity.
EOF
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $field = $input->getArgument('domain-field-name');
        $rights = $input->getOption('right');

        $doctrine = $this->getContainer()->get('doctrine');
        $identityType = strtolower($input->getArgument('identity-type'));
        $identity = $input->getArgument('identity-name');
        $identityClass = $this->getClassname($this->getContainer()->getParameter('sonatra_security.'.$identityType.'_class'));
        $em = $doctrine->getManagerForClass($identityClass);

        if (null === $em) {
            throw new InvalidConfigurationException(sprintf('The class "%s" is not supported by the doctrine manager. Change the "sonatra_security.%s_class" config', $identityClass, $identityType));
        }

        $identityRepo = $em->getRepository($identityClass);
        $domainClass = $this->getClassname($input->getArgument('domain-class-name'));
        $domain = new ObjectIdentity('class', $domainClass);
        $domainId = $input->getOption('domainid');
        $domainType = null !== $domainId ? 'object' : 'class';

        if (!in_array($identityType, array('role', 'group', 'user'))) {
            throw new InvalidArgumentException('The "identity-type" argument must be "role", "group" or "user"');

        } elseif ('user' === $identityType) {
            $identity = $identityRepo->findOneBy(array('username' => $identity));

        } elseif ('group' === $identityType) {
            $identity = $identityRepo->findOneBy(array('name' => $identity));

        } else {
            $identity = new Role($identity);
        }

        if (null === $identity) {
            throw new InvalidArgumentException(sprintf('Identity instance "%s" on "%s" not found', $input->getArgument('identity-name'), $identityClass));
        }

        // get the domain instance
        if (null !== $domainId) {
            $domain = new ObjectIdentity($domainId, $domainClass);
        }

        // revoke domain rights
        if (null === $field) {
            $this->revokeRights($output, $identity, $rights, $domainType, $domain);

        // revoke domain field rights
        } else {
            $this->revokeFieldRights($output, $identity, $rights, $domainType, $domain, $field);
        }
    }

    /**
     * Get classname from an entity name formated on the symfony way.
     *
     * @param string $entityName
     *
     * @return string The FQCN
     */
    private function getClassname($entityName)
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
     * Revoke rights on domain.
     *
     * @param OutputInterface $output     The output instance
     * @param mixed           $identity   The identifier instance
     * @param array           $rights     The list of right
     * @param string          $domainType The domain type (class or object)
     * @param mixed           $domain     The classname or object instance
     */
    private function revokeRights(OutputInterface $output, $identity, $rights, $domainType, $domain)
    {
        $aclManipulator = $this->getContainer()->get('sonatra_security.acl.manipulator');
        $revokeMethod = 'revoke'.ucfirst($domainType).'Permission';
        $deleteMethod = 'delete'.ucfirst($domainType).'Permissions';
        $getMethod = 'get'.ucfirst($domainType).'Permission';

        if (empty($rights)) {
            $aclManipulator->$deleteMethod($identity, $domain);

        } else {
            $aclManipulator->$revokeMethod($identity, $domain, $rights);
        }

        // display new rights
        $mask = $aclManipulator->$getMethod($identity, $domain);
        $rights = AclUtils::convertToAclName($mask);
        $output->writeln(array('', "<info>Remaining $domainType rights:</info> [ ".implode(', ', $rights)." ]"));
    }

    /**
     * Revoke rights on domain field.
     *
     * @param OutputInterface $output     The output instance
     * @param mixed           $identity   The identifier instance
     * @param array           $rights     The list of right
     * @param string          $domainType The domain type (class or object)
     * @param mixed           $domain     The classname or object instance
     * @param string          $field      The field name
     */
    private function revokeFieldRights(OutputInterface $output, $identity, $rights, $domainType, $domain, $field)
    {
        $aclManipulator = $this->getContainer()->get('sonatra_security.acl.manipulator');
        $revokeMethod = 'revoke'.ucfirst($domainType).'FieldPermission';
        $deleteMethod = 'delete'.ucfirst($domainType).'FieldPermissions';
        $getMethod = 'get'.ucfirst($domainType).'FieldPermission';

        if (empty($rights)) {
            $aclManipulator->$deleteMethod($identity, $domain, $field);

        } else {
            $aclManipulator->$revokeMethod($identity, $domain, $field, $rights);
        }

        // display new rights
        $mask = $aclManipulator->$getMethod($identity, $domain, $field);
        $rights = AclUtils::convertToAclName($mask);
        $output->writeln(array('', "<info>Remaining $domainType field rights:</info> [ ".implode(', ', $rights)." ]"));
    }
}
