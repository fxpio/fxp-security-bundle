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
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class AclGroupRemoveCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('acl:group:remove')
        ->setDescription('Removes a Group')
        ->setDefinition(array(
                new InputArgument('group-name', InputArgument::REQUIRED, 'The group name to delete'),
         ));
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $groupClass = str_replace('/', '\\', $this->getContainer()->getParameter('sonatra_security.group_class'));
            $groupName = $input->getArgument('group-name');

            $em = $this->getContainer()->get('doctrine')->getManagerForClass($groupClass);
            $repo = $em->getRepository($groupClass);
            $group = $repo->findOneBy(array('name' => $groupName));

            if (null === $group) {
                throw new \InvalidArgumentException("The group '$groupName' does not exist");
            }

            $em->remove($group);
            $em->flush();

            $output->writeln(array('', "The group <info>$groupName</info> was successfully removed"));

        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
}
