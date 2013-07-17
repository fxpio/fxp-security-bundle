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

use Symfony\Component\Security\Core\Role\Role;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Acl\Voter\FieldVote;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;

/**
 * Display the identifier rights of class/field.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class AclRightCommand extends ContainerAwareCommand
{
    protected $rightsDisplayed = array('VIEW', 'CREATE', 'EDIT',
                'DELETE', 'UNDELETE', 'OPERATOR', 'MASTER', 'OWNER', 'IDDQD',);

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('acl:right')
        ->setDescription('Gets the rights for a specified class and identifier, and optionnally for a given field')
        ->setDefinition(array(
                new InputArgument('identity-type', InputArgument::REQUIRED, 'The security identity type (role, user)'),
                new InputArgument('identity-name', InputArgument::REQUIRED, 'The security identity name to use for the right'),
                new InputArgument('domain-class-name', InputArgument::REQUIRED, 'The domain class name to get the right for'),
                new InputArgument('domain-field-name', InputArgument::OPTIONAL, 'The domain class field name to get the right for'),
                new InputOption('domainid', null, InputOption::VALUE_REQUIRED, 'This domain id (only for object)'),
                new InputOption('security-identity', null, InputOption::VALUE_REQUIRED, 'This security identity type', 'role'),
                new InputOption('host', null, InputOption::VALUE_REQUIRED, 'The hostname pattern (for default anonymous role)'),
                new InputOption('calc', 'c', InputOption::VALUE_NONE, 'Get the rights with granted method (calculated)')
        ))
        ->setHelp(<<<EOF
The <info>acl:right</info> command gets the existing rights for the
given security identity on a specified domain (class or object).
EOF
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $field = $input->getArgument('domain-field-name');
        $calculated = $input->getOption('calc');

        $doctrine = $this->getContainer()->get('doctrine');
        $domainClass = $this->getClassname($input->getArgument('domain-class-name'));
        $domainType = null !== $input->getOption('domainid') ? 'object' : 'class';
        $domain = $input->getOption('domainid');
        $identityType = strtolower($input->getArgument('identity-type'));
        $identity = $input->getArgument('identity-name');
        $identityName = $identity;
        $identityClass = $this->getClassname($this->getContainer()->getParameter('sonatra_security.'.$identityType.'_class'));
        $identityRepo = $doctrine->getManagerForClass($identityClass)->getRepository($identityClass);
        $identity = $identityRepo->findOneBy(array(('user' === $identityType ? 'username' : 'name') => $identity));
        $host = $input->getOption('host');

        if (!in_array($identityType, array('role', 'user'))) {
            throw new \InvalidArgumentException("The 'identity-type' argument must be 'role' or 'user'");
        }

        if (null === $identity) {
            throw new \InvalidArgumentException("Identity instance '".$input->getArgument('identity-name')."' on '$identityClass' not found");
        }

        // get the domain instance
        if ('object' === $domainType) {
            $domainRepo = $doctrine->getManagerForClass($domainClass)->getRepository($domainClass);
            $domain = $domainRepo->findOneBy(array('id' => $domain));

            if (null === $domain) {
                throw new \InvalidArgumentException("Domain instance '".$input->getOption('domainid')."' on '$domainClass' not found");
            }

        } else {
            $domain = $domainClass;
        }

        // display field permissions
        if (null !== $field && !$calculated) {
            $this->displayFieldPermissions($output, $domainType, $domain, $field, $identity, $identityName, $identityType);

            return;
        }

        // display calculated field permissions
        if (null !== $field) {
            $this->displayCalculatedFieldPermissions($output, $domainType, $domain, $field, $identity, $identityName, $identityType, $host);

            return;
        }

        // display class permissions
        if (!$calculated) {
            $this->displayClassPermissions($output, $domainType, $domain, $identity, $identityName, $identityType);

            return;
        }

        $this->displayCalculatedClassPermissions($output, $domainType, $domain, $identity, $identityName, $identityType, $host);
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

        return $entityName;
    }

    /**
     * Display domain field permissions.
     *
     * @param OutputInterface $output
     * @param string          $domainType
     * @param mixed           $domain
     * @param string          $field
     * @param mixed           $identity
     * @param string          $identityName
     * @param string          $identityType
     */
    private function displayFieldPermissions(OutputInterface $output, $domainType, $domain, $field, $identity, $identityName, $identityType)
    {
        $aclManager = $this->getContainer()->get('sonatra.acl.manager');
        $mask = $aclManager->getClassFieldPermission($identity, $domain, $field);
        $className = is_object($domain) ? get_class($domain) : $domain;
        $fieldRights = $aclManager->convertToAclName($mask);

        $output->writeln(array(
                '',
                sprintf("Rights of <info>%s</info>:<comment>%s</comment> field for <info>%s</info> $identityType:", $className, $field, $identityName),
                '',
                sprintf("    <comment>%s</comment> : [ <info>%s</info> ]", $field, implode(", ", $fieldRights)),
        ));
    }

    /**
     * Display calculated domain field permissions.
     *
     * @param OutputInterface $output
     * @param string          $domainType
     * @param mixed           $domain
     * @param string          $field
     * @param mixed           $identity
     * @param string          $identityName
     * @param string          $identityType
     * @param string          $host
     */
    private function displayCalculatedFieldPermissions(OutputInterface $output, $domainType, $domain, $field, $identity, $identityName, $identityType, $host = null)
    {
        $sc = $this->getContainer()->get('security.context');
        $className = is_object($domain) ? get_class($domain) : $domain;
        $fieldRights = array();

        // inject token
        if ('user' === $identityType) {
            $sc->setToken(new AnonymousToken('key', $identity, $this->getHostRoles($host, $identity->getRoles())));

        } else {
            $sc->setToken(new AnonymousToken('key', 'console.', $this->getHostRoles($host, array($identity))));
        }

        foreach ($this->rightsDisplayed as $right) {
            if ($sc->isGranted($right, new FieldVote($domain, $field))) {
                $fieldRights[] = $right;
            }
        }

        $output->writeln(array(
                '',
                sprintf("Rights of <info>%s</info>:<comment>%s</comment> field for <info>%s</info> $identityType:", $className, $field, $identityName),
                '',
                sprintf("    <comment>%s</comment> : [ <info>%s</info> ]", $field, implode(", ", $fieldRights)),
        ));
    }

    /**
     * Display domain permissions.
     *
     * @param OutputInterface $output
     * @param string          $domainType
     * @param mixed           $domain
     * @param mixed           $identity
     * @param string          $identityName
     * @param string          $identityType
     */
    private function displayClassPermissions(OutputInterface $output, $domainType, $domain, $identity, $identityName, $identityType)
    {
        $aclManager = $this->getContainer()->get('sonatra.acl.manager');
        $className = $domain;
        $getMethod = 'getClassPermission';
        $getFieldMethod = 'getClassFieldPermission';

        if (is_object($domain)) {
            $className = get_class($domain);
            $getMethod = 'getObjectPermission';
            $getFieldMethod = 'getObjectFieldPermission';
        }

        // get class rights
        $mask = $aclManager->$getMethod($identity, $domain);
        $classRights = $aclManager->convertToAclName($mask);

        // get properties rights
        $reflClass = new \ReflectionClass($className);
        $properties = array();

        foreach ($reflClass->getProperties() as $property) {
            $mask = $aclManager->$getFieldMethod($identity, $className, $property->name);
            $properties[$property->name] = $aclManager->convertToAclName($mask);
        }

        // calculated width class
        $width = 0;

        foreach ($this->rightsDisplayed as $right) {
            $width = strlen($right) > $width ? strlen($right) : $width;
        }

        // calculated width properties
        foreach ($properties as $property => $rights) {
            $width = strlen($property) > $width ? strlen($property) : $width;
        }

        // display class infos
        $output->writeln(array('', sprintf("Rights of <comment>%s</comment> $domainType for <info>%s</info> $identityType:", $className, $identityName)));
        $output->writeln(array('', '  Class rights:'));

        foreach ($this->rightsDisplayed as $right) {
            $output->writeln(
                    sprintf("    <comment>%-${width}s</comment> : <info>%s</info>", $right, in_array($right, $classRights) ? 'true': 'false')
            );
        }
        $output->writeln('');

        // displa class field infos
        $output->writeln(sprintf("  Fields rights:"));
        ksort($properties);

        foreach ($properties as $field => $rights) {
            $output->writeln(sprintf("    <comment>%-${width}s</comment> : [ <info>%s</info> ]", $field, implode(", ", $rights)));
        }

        // init children group
        if (method_exists($identity, 'getGroups')) {
            $childrenGroup = array();

            foreach ($identity->getGroups() as $child) {
                $childrenGroup[] = (is_string($child)) ? $child : $child->getName();
            }

            // calculated width children group
            foreach ($childrenGroup as $name => $status) {
                $width = strlen($name) > $width ? strlen($name) : $width;
            }

            // display children group infos
            $output->writeln(array('',sprintf("  Children group:")));
            sort($childrenGroup);

            foreach ($childrenGroup as $group) {
                $output->writeln(sprintf("    <comment>%-${width}s</comment>", $group));
            }

            if (0 === count($childrenGroup)) {
                $output->writeln("    <comment>Has not children</comment>");
            }
        }

        // init children role
        $childrenRole = array();

        foreach ($identity->getRoles() as $child) {
            $childrenRole[] = (is_string($child)) ? $child : $child->getRole();
        }

        // calculated width children role
        foreach ($childrenRole as $name => $status) {
            $width = strlen($name) > $width ? strlen($name) : $width;
        }

        // display children role infos
        $output->writeln(array('',sprintf("  Children role:")));
        sort($childrenRole);

        foreach ($childrenRole as $role) {
            $output->writeln(sprintf("    <comment>%-${width}s</comment>", $role));
        }

        if (0 === count($childrenRole)) {
            $output->writeln("    <comment>Has not children</comment>");
        }
    }

    /**
     * Display domain permissions.
     *
     * @param OutputInterface $output
     * @param string          $domainType
     * @param mixed           $domain
     * @param mixed           $identity
     * @param string          $identityName
     * @param string          $identityType
     * @param string          $host
     */
    private function displayCalculatedClassPermissions(OutputInterface $output, $domainType, $domain, $identity, $identityName, $identityType, $host = null)
    {
        $sc = $this->getContainer()->get('security.context');

        // inject token
        if ('user' === $identityType) {
            $sc->setToken(new AnonymousToken('key', $identity, $this->getHostRoles($host, $identity->getRoles())));

        } else {
            $sc->setToken(new AnonymousToken('key', 'console.', $this->getHostRoles($host, array($identity))));
        }

        // get properties rights
        $className = is_object($domain) ? get_class($domain) : $domain;
        $reflClass = new \ReflectionClass($className);
        $properties = array();

        foreach ($reflClass->getProperties() as $property) {
            $properties[$property->name] = array();

            foreach ($this->rightsDisplayed as $right) {
                if ($sc->isGranted($right, new FieldVote($domain, $property->name))) {
                    $properties[$property->name][] = $right;
                }
            }
        }

        // calculated width class
        $width = 0;

        foreach ($this->rightsDisplayed as $right) {
            $width = strlen($right) > $width ? strlen($right) : $width;
        }

        // calculated width properties
        foreach ($properties as $property => $rights) {
            $width = strlen($property) > $width ? strlen($property) : $width;
        }

        // display class infos
        $output->writeln(array('', sprintf("Rights of <comment>%s</comment> $domainType for <info>%s</info> $identityType:", $className, $identityName)));
        $output->writeln(array('', '  Class rights:'));

        foreach ($this->rightsDisplayed as $right) {
            $value = $sc->isGranted($right, $domain) ? 'true': 'false';
            $output->writeln(
                    sprintf("    <comment>%-${width}s</comment> : <info>%s</info>", $right, $value)
            );
        }
        $output->writeln('');

        // displa class field infos
        $output->writeln(sprintf("  Fields rights:"));
        ksort($properties);

        foreach ($properties as $field => $rights) {
            $output->writeln(sprintf("    <comment>%-${width}s</comment> : [ <info>%s</info> ]", $field, implode(", ", $rights)));
        }

        // init children group
        $childrenGroup = array();

        if (method_exists($identity, 'getGroups')) {
            foreach ($identity->getGroups() as $child) {
                $childrenGroup[$child->getName()] = $child;
            }

            // calculated width children group
            foreach ($childrenGroup as $child) {
                $width = strlen($child->getName()) > $width ? strlen($child->getName()) : $width;
            }

            // display children group infos
            $output->writeln(array('',sprintf("  Children group:")));
            ksort($childrenGroup);

            foreach ($childrenGroup as $group) {
                $output->writeln(sprintf("    <comment>%-${width}s</comment>", $group->getName()));
            }

            if (0 === count($childrenGroup)) {
                $output->writeln("    <comment>Has not children</comment>");
            }
        }

        // init children role
        $childrenRole = array();
        $directRoles = $identity->getRoles();
        $subChildrenRole = array();

        if (is_object($directRoles)) {
            $directRoles = $directRoles->toArray();
        }

        if ($this->getContainer()->has('sonatra.security.role_hierarchy')) {
            $subChildrenRole = $this->getContainer()->get('sonatra.security.role_hierarchy')->getReachableRoles($directRoles);
        }

        // add indirect token roles
        foreach ($sc->getToken()->getRoles() as $role) {
            $role = is_string($role) ? $role : $role->getRole();
            $childrenRole[$role] = 'indirect token';
        }

        // add indirect group children role
        foreach ($childrenGroup as $group) {
            foreach ($group->getRoles() as $role) {
                $role = is_string($role) ? $role : $role->getRole();
                $childrenRole[$role] = 'indirect group';
            }
        }

        // add indirect children role
        foreach ($subChildrenRole as $role) {
            $role = is_string($role) ? $role : $role->getRole();
            $childrenRole[$role] = 'indirect';
        }

        // add direct children role
        foreach ($directRoles as $role) {
            $role = is_string($role) ? $role : $role->getRole();
            $childrenRole[$role] = 'direct';
        }

        // calculated width children role
        foreach ($childrenRole as $name => $status) {
            $width = strlen($name) > $width ? strlen($name) : $width;
        }

        // display children role infos
        $output->writeln(array('',sprintf("  Children role:")));
        ksort($childrenRole);

        foreach ($childrenRole as $role => $status) {
            $output->writeln(sprintf("    <comment>%-${width}s</comment> : <info>%s</info>", $role, $status));
        }

        if (0 === count($childrenRole)) {
            $output->writeln("    <comment>Has not children</comment>");
        }
    }

    /**
     * Get the role added in token when host matched with anonymous role config.
     *
     * @param string $hostname
     * @param array  $roles
     *
     * @return array
     */
    protected function getHostRoles($hostname = null, $roles = array())
    {
        if (null === $hostname) {
            return $roles;
        }

        $anonymousRole = null;
        $rolesForHosts = $this->getContainer()->getParameter('sonatra_security.anonymous_authentication.hosts');

        foreach ($rolesForHosts as $host => $role) {
            if (preg_match('/.'.$host.'/', $hostname)) {
                $anonymousRole = $role;
                break;
            }
        }

        // find role for anonymous
        if (null !== $anonymousRole) {
            $roles[] = new Role($anonymousRole);
        }

        return array_unique($roles);
    }
}
