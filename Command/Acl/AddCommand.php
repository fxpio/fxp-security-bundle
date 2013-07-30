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
use Sonatra\Bundle\SecurityBundle\Acl\Domain\AclManager;

/**
 * Add domain (class or object) rights.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class AddCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('security:acl:add')
        ->setDescription('Add a specified right from a given identifier on a given domain (class or object).')
        ->setDefinition(array(
                new InputArgument('identity-type', InputArgument::REQUIRED, 'The security identity type (role, user)'),
                new InputArgument('identity-name', InputArgument::REQUIRED, 'The security identity name to use for the right'),
                new InputArgument('domain-class-name', InputArgument::REQUIRED, 'The domain class name to get the right for'),
                new InputArgument('domain-field-name', InputArgument::OPTIONAL, 'The domain class field name to get the right for'),
                new InputOption('domainid', null, InputOption::VALUE_REQUIRED, 'This domain id (only for object)'),
                new InputOption('index', null, InputOption::VALUE_REQUIRED, 'The ACE order', 0),
                new InputOption('granting', null, InputOption::VALUE_REQUIRED, 'The ACE granting', true),
                new InputOption('rule', null, InputOption::VALUE_REQUIRED, 'The ACE granting rule', 'all'),
                new InputOption('right', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                        'Specifies the right(s) to set on the given class for the given security identity')
        ))
        ->setHelp(<<<EOF
The <info>acl:right:add</info> command adds the given rights for the
given security identity on a specified domain (class or object).

If the "right" option isn't specified, then all the available rights will be set
on the domain for the security identity.
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
        $index = (int) $input->getOption('index');
        $granting = (bool) $input->getOption('granting');
        $rule = $input->getOption('rule');

        if (empty($rights)) {
            $rights = array(
                    AclManager::VIEW,
                    AclManager::CREATE,
                    AclManager::EDIT,
                    AclManager::DELETE,
                    AclManager::UNDELETE
            );

            if (null !== $field) {
                $rights = array(
                        AclManager::VIEW,
                        AclManager::CREATE,
                        AclManager::EDIT
                );
            }
        }

        $doctrine = $this->getContainer()->get('doctrine');
        $domainClass = $this->getClassname($input->getArgument('domain-class-name'));
        $domainType = null !== $input->getOption('domainid') ? 'object' : 'class';
        $domain = $input->getOption('domainid');
        $identityType = strtolower($input->getArgument('identity-type'));
        $identity = $input->getArgument('identity-name');
        $identityClass = $this->getClassname($this->getContainer()->getParameter('sonatra_security.'.$identityType.'_class'));
        $identityRepo = $doctrine->getManagerForClass($identityClass)->getRepository($identityClass);
        $identity = $identityRepo->findOneBy(array(('user' === $identityType ? 'username' : 'name') => $identity));

        if (!in_array($identityType, array('role', 'user'))) {
            throw new \InvalidArgumentException('The "identity-type" argument must be "role" or "user"');
        }

        if (null === $identity) {
            throw new \InvalidArgumentException(sprintf('Identity instance "%s" on "%s" not found', $input->getArgument('identity-name'), $identityClass));
        }

        // get the domain instance
        if ('object' === $domainType) {
            $domainRepo = $doctrine->getManagerForClass($domainClass)->getRepository($domainClass);
            $domain = $domainRepo->findOneBy(array('id' => $domain));

            if (null === $domain) {
                throw new \InvalidArgumentException(sprintf('Domain instance "%s" on "%s" not found', $input->getOption('domainid'), $domainClass));
            }

        } else {
            $domain = $domainClass;
        }

        // add domain rights
        if (null === $field) {
            $this->addRights($output, $identity, $rights, $domainType, $domain, $index, $granting, $rule);

        // add domain field rights
        } else {
            $this->addFieldRights($output, $identity, $rights, $domainType, $domain, $field, $index, $granting, $rule);
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
     * Add rights on domain.
     *
     * @param OutputInterface $output     The output instance
     * @param mixed           $identity   The identifier instance
     * @param array           $rights     The list of right
     * @param string          $domainType The domain type (class or object)
     * @param mixed           $domain     The classname or object instance
     * @param integer         $index      The ACE index
     * @param boolean         $granting   The ACE granting
     * @param string          $rule       The ACE granting rule
     */
    private function addRights(OutputInterface $output, $identity, $rights, $domainType, $domain, $index, $granting, $rule)
    {
        $aclManager = $this->getContainer()->get('sonatra.acl.manager');
        $addMethod = 'add'.ucfirst($domainType).'Permission';
        $getMethod = 'get'.ucfirst($domainType).'Permission';
        $aclManager->$addMethod($identity, $domain, $rights, $index, $granting, $rule);

        // display new rights
        $mask = $aclManager->$getMethod($identity, $domain);
        $rights = $aclManager->convertToAclName($mask);
        $output->writeln(array('', "<info>Newing $domainType rights:</info> [ ".implode(', ', $rights)." ]"));
    }

    /**
     * Add rights on domain field.
     *
     * @param OutputInterface $output     The output instance
     * @param mixed           $identity   The identifier instance
     * @param array           $rights     The list of right
     * @param string          $domainType The domain type (class or object)
     * @param mixed           $domain     The classname or object instance
     * @param string          $field      The field name
     * @param integer         $index      The ACE index
     * @param boolean         $granting   The ACE granting
     * @param string          $rule       The ACE granting rule
     */
    private function addFieldRights(OutputInterface $output, $identity, $rights, $domainType, $domain, $field, $index, $granting, $rule)
    {
        $aclManager = $this->getContainer()->get('sonatra.acl.manager');
        $addMethod = 'add'.ucfirst($domainType).'FieldPermission';
        $getMethod = 'get'.ucfirst($domainType).'FieldPermission';
        $aclManager->$addMethod($identity, $domain, $field, $rights, $index, $granting, $rule);

        // display new rights
        $mask = $aclManager->$getMethod($identity, $domain, $field);
        $rights = $aclManager->convertToAclName($mask);
        $output->writeln(array('', "<info>Newing $domainType field rights:</info> [ ".implode(', ', $rights)." ]"));
    }
}
