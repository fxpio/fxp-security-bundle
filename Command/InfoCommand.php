<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\SecurityBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
abstract class InfoCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setDefinition(array(
                new InputArgument('name', InputArgument::OPTIONAL, 'The name'),
                new InputOption('host', null, InputOption::VALUE_REQUIRED, 'The hostname pattern (for default anonymous role)', 'localhost'),
                new InputOption('no-host', null, InputOption::VALUE_NONE, 'Not display the role of host'),
                new InputOption('calc', 'c', InputOption::VALUE_NONE, 'Get all roles of hierarchical role (calculated)')
         ));
    }

    /**
     * {@inheritdoc}
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        if (!$input->getArgument('name')) {
            $name = $this->getHelper('dialog')->askAndValidate(
                    $output,
                    'Please choose a name:',
                    function($name) {
                        if (empty($name)) {
                            throw new \Exception('Name can not be empty');
                        }

                        return $name;
                    }
            );

            $input->setArgument('name', $name);
        }
    }

    /**
     * Sort the status list with name list.
     *
     * @param array $names
     * @param array $status
     *
     * @return array
     */
    protected function sortRecords(array $names, array $status)
    {
        $list = array();
        sort($names);

        foreach ($names as $name) {
            $list[$name] = $status[$name];
        }

        return $list;
    }

    /**
     * Render infos in output console.
     *
     * @param OutputInterface $output
     * @param array           $list
     * @param string          $title
     * @param string|null     $message       The message for empty list
     * @param int             $width
     * @param boolean         $emptyRendered
     */
    protected function renderInfos(OutputInterface $output, array $list,
            $title, $message = null, $width = 0, $emptyRendered = false)
    {
        if (!$emptyRendered && 0 === count($list)) {
            return;
        }

        $output->writeln(array('',sprintf('  %s:', $title)));

        foreach ($list as $name => $status) {
            $output->writeln(sprintf("    <comment>%-${width}s</comment> : <info>%s</info>", $name, $status));
        }

        if (0 === count($list)) {
            $output->writeln(sprintf('    <comment>%s</comment>', $message));
        }
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

        $roles = array();
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
            $roles[$anonymousRole] = 'token';
        }

        return $roles;
    }
}
