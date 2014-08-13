<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\SecurityBundle\Command\Host;

use Sonatra\Bundle\SecurityBundle\Command\AbstractInfoCommand;
use Sonatra\Bundle\SecurityBundle\Core\Token\ConsoleToken;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Core\Role\RoleInterface;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class InfoHostCommand extends AbstractInfoCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();

        $this->setName('security:host:info')
            ->setDescription('Security infos of host role')
            ->setDefinition(array(
                new InputArgument('name', InputArgument::OPTIONAL, 'The name', 'localhost'),
                new InputOption('calc', 'c', InputOption::VALUE_NONE, 'Get all roles of role reachable (calculated)')
         ));
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $hostname = $input->getArgument('name');
        $calculated = $input->getOption('calc');
        $allRoles = $this->getHostRoles($hostname);

        // get calculated roles
        if ($calculated) {
            $tokenRoles = array_keys($allRoles);
            $token = new ConsoleToken('key', 'console.', $tokenRoles);
            $roles = $this->getContainer()->get('security.role_hierarchy')->getReachableRoles($token->getRoles());

            foreach ($roles as $role) {
                if ($role instanceof RoleInterface) {
                    if (!array_key_exists($role->getRole(), $allRoles)) {
                        $allRoles[$role->getRole()] = 'role hierarchy';
                    }
                }
            }
        }

        // prepare render
        $width = 0;
        $roles = array();

        foreach ($allRoles as $name => $status) {
            $width = strlen($name) > $width ? strlen($name) : $width;
            $roles[] = $name;
        }

        $roles = $this->sortRecords($roles, $allRoles);
        $hostname = $this->getContainer()->get('request')->getUri();

        // render
        $output->writeln(array('', sprintf('Security context for <info>%s</info> host:', $hostname)));
        $this->renderInfos($output, $roles, 'Roles', 'Contains no associated role', $width, true);
    }
}
