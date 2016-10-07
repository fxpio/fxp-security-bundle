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
 * Add domain (class or object) rights.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class AddAclCommand extends AbstractAclActionCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();

        $this->setName('security:acl:add')
        ->setDescription('Add a specified right from a given identifier on a given domain (class or object).')
        ->addOption('index', null, InputOption::VALUE_REQUIRED, 'The ACE order', 0)
        ->addOption('granting', null, InputOption::VALUE_REQUIRED, 'The ACE granting', true)
        ->addOption('strategy', null, InputOption::VALUE_REQUIRED, 'The ACE granting strategy', 'all')
        ->addOption('right', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
            'Specifies the right(s) to set on the given class for the given security identity')
        ->setHelp(<<<'EOF'
The <info>security:acl:add</info> command adds the given rights for the
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
        $rights = $this->getRights($input->getOption('right'), $field, true);
        $domain = $this->getDomain($input);
        $domainType = $this->getDomainType($domain);
        $identity = $this->getIdentity($input);
        $index = (int) $input->getOption('index');
        $granting = (bool) $input->getOption('granting');
        $strategy = $input->getOption('strategy');

        $this->addRights($output, $identity, $rights, $domainType, $domain, $field, $index, $granting, $strategy);
    }
}
