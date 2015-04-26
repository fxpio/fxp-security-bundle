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

use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Voter\FieldVote;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Role\RoleInterface;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use FOS\UserBundle\Model\GroupInterface;
use Sonatra\Bundle\SecurityBundle\Core\Token\ConsoleToken;
use Sonatra\Bundle\SecurityBundle\Acl\Util\AclUtils;
use Sonatra\Bundle\SecurityBundle\Exception\InvalidArgumentException;

/**
 * Display the identifier rights of class/field.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class InfoAclCommand extends AbstractAclActionCommand
{
    protected $rightsDisplayed = array(
        'VIEW',
        'CREATE',
        'EDIT',
        'DELETE',
        'UNDELETE',
        'OPERATOR',
        'MASTER',
        'OWNER',
    );

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();

        $this->setName('security:acl:info')
        ->setDescription('Gets the rights for a specified class and identifier, and optionally for a given field')
        ->addOption('host', null, InputOption::VALUE_REQUIRED, 'The hostname pattern (for default anonymous role)', 'localhost')
        ->addOption('no-host', null, InputOption::VALUE_NONE, 'Not display the role of host')
        ->addOption('calc', 'c', InputOption::VALUE_NONE, 'Get the rights with granted method (calculated)')
        ->setHelp(<<<EOF
The <info>security:acl:info</info> command gets the existing rights for the
given security identity on a specified domain (class or object).
EOF
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $doctrine = $this->getContainer()->get('doctrine');
        $identityType = strtolower($input->getArgument('identity-type'));
        $identity = $input->getArgument('identity-name');
        $identityName = $identity;
        $identityClass = $this->getClassname($this->getContainer()->getParameter('sonatra_security.'.$identityType.'_class'));
        $em = $doctrine->getManagerForClass($identityClass);

        if (null === $em) {
            throw new InvalidConfigurationException(sprintf('The class "%s" is not supported by the doctrine manager. Change the "sonatra_security.%s_class" config', $identityClass, $identityType));
        }

        /* @var EntityRepository $identityRepo */
        $identityRepo = $em->getRepository($identityClass);
        $domainClass = $this->getClassname($input->getArgument('domain-class-name'));
        $domain = new ObjectIdentity('class', $domainClass);
        $domainId = $input->getOption('domainid');
        $field = $input->getArgument('domain-field-name');
        $fields = null === $field ? array() : array($field);
        $noHost = $input->getOption('no-host');
        $host = $noHost ? null : $input->getOption('host');
        $calculated = $input->getOption('calc');

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

        // init get acl rights
        $classRights = array();
        $fieldRights = array();
        $aclManipulator = $this->getContainer()->get('sonatra_security.acl.manipulator');

        if (null === $field) {
            $reflClass = new \ReflectionClass($domainClass);

            foreach ($reflClass->getProperties() as $property) {
                $fields[] = $property->name;
            }
        }

        // check with all acl voter
        if ($calculated) {
            $sc = $this->getContainer()->get('security.context');

            if ($identity instanceof UserInterface) {
                $roles = array_merge($identity->getRoles(), $this->getHostRoles($host));
                $sc->setToken(new UsernamePasswordToken($identity, '', 'key', $roles));
            } elseif ($identity instanceof GroupInterface) {
                $roles = array_merge($identity->getRoles(), $this->getHostRoles($host));
                $sc->setToken(new ConsoleToken('key', '', $roles));
            } elseif ($identity instanceof RoleInterface) {
                $roles = array_merge(array($identityName), $this->getHostRoles($host));
                $sc->setToken(new ConsoleToken('key', '', $roles));
            }

            // get class rights
            foreach ($this->rightsDisplayed as $right) {
                if ($sc->isGranted($right, $domain)) {
                    $classRights[] = $right;
                }
            }

            // get fields rights
            foreach ($fields as $cField) {
                $fieldRights[$cField] = array();

                foreach ($this->rightsDisplayed as $right) {
                    if ($sc->isGranted($right, new FieldVote($domain, $cField))) {
                        $fieldRights[$cField][] = $right;
                    }
                }
            }

        // check with only ACL stored in table
        } else {
            $getMethod = 'getClassPermission';
            $getFieldMethod = 'getClassFieldPermission';

            if ('class' !== $domain->getIdentifier()) {
                $getMethod = 'getObjectPermission';
                $getFieldMethod = 'getObjectFieldPermission';
            }

            // get class rights
            $classMask = $aclManipulator->$getMethod($identity, $domain);
            $classRights = AclUtils::convertToAclName($classMask);

            // get fields rights
            foreach ($fields as $cField) {
                $fieldMask = $aclManipulator->$getFieldMethod($identity, $domain, $cField);
                $fieldRights[$cField] = AclUtils::convertToAclName($fieldMask);
            }
        }

        // display title
        $out = array(
            '',
            $this->formatTitle($identityType, $identityName, $domainClass, $domainId, $field),
        );

        //display class
        $out = array_merge($out, $this->formatClass($classRights, (null !== $field)));

        // display fields
        $out = array_merge($out, $this->formatFields($fieldRights, (null !== $field)));

        $output->writeln($out);
    }

    /**
     * Display command title.
     *
     * @param string     $identityType
     * @param string     $identityName
     * @param string     $domainName
     * @param string|int $domainId
     * @param string     $domainField
     *
     * @return string The text for output console
     */
    protected function formatTitle($identityType, $identityName, $domainName, $domainId = null, $domainField = null)
    {
        $type = ' class';

        if (null !== $domainField) {
            $type = sprintf(':<comment>%s</comment> field', $domainField);

            if (null !== $domainId) {
                $type = sprintf(':<comment>%s</comment> field of class instance <comment>%s</comment>', $domainField, $domainId);
            }
        } elseif (null !== $domainId) {
            $type = sprintf(' class instance <comment>%s</comment>', $domainId);
        }

        return sprintf('Rights of <info>%s</info>%s for <info>%s</info> %s:', $domainName, $type, $identityName, $identityType);
    }

    /**
     * Format class rights section.
     *
     * @param array $classRights
     * @param bool  $hide
     *
     * @return array The text for output console
     */
    protected function formatClass(array $classRights, $hide = false)
    {
        if ($hide) {
            return array();
        }

        $out = array('', '  Class rights:');
        $rights = array();
        $width = 0;

        // calculated width right names
        foreach ($this->rightsDisplayed as $right) {
            $width = strlen($right) > $width ? strlen($right) : $width;
        }

        foreach ($this->rightsDisplayed as $right) {
            $value = in_array($right, $classRights) ? 'true': 'false';
            $rights[] = sprintf("    <comment>%-${width}s</comment> : <info>%s</info>", $right, $value);
        }

        return array_merge($out, $rights);
    }

    /**
     * Format fields rights section.
     *
     * @param array   $fieldRights
     * @param boolean $hideTitle
     *
     * @return array The text for output console
     */
    protected function formatFields(array $fieldRights, $hideTitle = false)
    {
        if (0 === count($fieldRights)) {
            return array();
        }

        $out = array('');
        $fields = array();
        $width = 0;

        if (!$hideTitle) {
            $out = array_merge($out, array('  Fields rights:'));
        }

        // calculated width field names
        foreach ($fieldRights as $field => $rights) {
            $width = strlen($field) > $width ? strlen($field) : $width;
        }

        // display fields
        foreach ($fieldRights as $field => $rights) {
            $fields[] = sprintf("    <comment>%-${width}s</comment> : [ <info>%s</info> ]", $field, implode(", ", $rights));
        }

        return array_merge($out, $fields);
    }

    /**
     * Get the roles of host matched with anonymous role config.
     *
     * @param string|null $hostname
     *
     * @return array
     */
    protected function getHostRoles($hostname = null)
    {
        if (null === $hostname) {
            return array();
        }

        if (false === strpos($hostname, '://')) {
            $hostname = 'http://'.$hostname;
        }

        $request = Request::create($hostname);

        /* @var Application $application */
        $application = $this->getApplication();

        $this->getContainer()->set('request', $request);
        $this->getContainer()->enterScope('request');

        $event = new GetResponseEvent($application->getKernel(), $request, HttpKernelInterface::MASTER_REQUEST);
        $this->getContainer()->get('security.firewall')->onKernelRequest($event);

        $roles = array();
        /* @var TokenInterface|null $token */
        $token = $this->getContainer()->get('security.context')->getToken();

        if (null !== $token) {
            foreach ($token->getRoles() as $role) {
                $roles[] = $role;
            }
        }

        return $roles;
    }
}
