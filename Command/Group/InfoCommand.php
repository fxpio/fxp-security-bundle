<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\SecurityBundle\Command\Group;

use Doctrine\ORM\EntityRepository;
use Sonatra\Bundle\SecurityBundle\Command\InfoCommand as BaseInfoCommand;
use Sonatra\Bundle\SecurityBundle\Core\Token\ConsoleToken;
use Sonatra\Bundle\SecurityBundle\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Core\Role\RoleInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use FOS\UserBundle\Model\GroupInterface;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class InfoCommand extends BaseInfoCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();

        $this->setName('security:group:info')
            ->setDescription('Security infos of group');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $doctrine = $this->getContainer()->get('doctrine');
        $identityClass = str_replace('/', '\\', $this->getContainer()->getParameter('sonatra_security.group_class'));
        $identityName = $input->getArgument('name');
        $em = $doctrine->getManagerForClass($identityClass);

        if (null === $em) {
            throw new InvalidConfigurationException(sprintf('The class "%s" is not supported by the doctrine manager. Change the "sonatra_security.group_class" config', $identityClass));
        }

        /* @var EntityRepository $identityRepo */
        $identityRepo = $em->getRepository($identityClass);
        $identity = $identityRepo->findOneBy(array('name' => $identityName));
        $noHost = $input->getOption('no-host');
        $host = $noHost ? null : $input->getOption('host');
        $calculated = $input->getOption('calc');

        if (null === $identity) {
            throw new InvalidArgumentException(sprintf('Group instance "%s" on "%s" not found', $identityName, $identityClass));
        }

        $output->writeln(array('', sprintf('Security context for <info>%s</info> group:', $identity->getName())));
        $this->displayInfos($output, $identity, $calculated, $host);
    }

    /**
     * Display infos directly attached on the identity.
     *
     * @param OutputInterface $output
     * @param object          $identity
     * @param boolean         $calculated
     * @param string          $host
     */
    protected function displayInfos(OutputInterface $output, $identity, $calculated = false, $host = null)
    {
        // get all roles
        $allRoles = $this->getHostRoles($host);

        if ($identity instanceof GroupInterface) {
            foreach ($identity->getRoles() as $child) {
                $child = ($child instanceof RoleInterface) ? $child->getRole() : $child;
                $allRoles[$child] = 'direct';
            }
        }

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

        // render
        $this->renderInfos($output, $roles, 'Roles', 'Contains no associated role', $width, true);
    }
}
