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
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Sonatra\Bundle\SecurityBundle\Exception\InvalidArgumentException;

/**
 * Remove entry (class or object) rights.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class RemoveCommand extends AbstractActionCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();

        $this->setName('security:acl:remove')
        ->setDescription('Remove a specified right from a given identifier on a given domain (class or object).')
        ->addOption('right', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
            'Specifies the right(s) to set on the given class for the given security identity')
        ->setHelp(<<<EOF
The <info>security:acl:remove</info> command revokes the given rights for the
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

        /* @var EntityRepository $identityRepo */
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

        // revoke rights
        $this->revokeRights($output, $identity, $rights, $domainType, $domain, $field);
    }
}
