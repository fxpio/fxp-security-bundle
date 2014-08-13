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

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Remove entry (class or object) rights.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class RemoveAclCommand extends AbstractAclActionCommand
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
        $rights = $this->getRights($input->getOption('right'), $field, false);
        $domain = $this->getDomain($input);
        $domainType = $this->getDomainType($domain);
        $identity = $this->getIdentity($input);

        $this->revokeRights($output, $identity, $rights, $domainType, $domain, $field);
    }
}
