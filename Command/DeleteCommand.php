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
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
abstract class DeleteCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setDefinition(array(
                new InputArgument('name', InputArgument::OPTIONAL, 'The name'),
         ));
    }

    /**
     * Execution of delete action
     *
     * @param string $entityClass The entity class name
     * @param string $entityName  The entity name
     * @param array  $filter      The filter for fond one by doctrine query
     *
     * @throws \InvalidArgumentException When entity does not exist
     */
    protected function doExecute(OutputInterface $output, $entityClass, $entityName, array $filter)
    {
        $entityClass = str_replace('/', '\\', $entityClass);
        $shortName = substr($entityClass, strrpos($entityClass, '\\') + 1);
        $em = $this->getContainer()->get('doctrine')->getManagerForClass($entityClass);
        $repo = $em->getRepository($entityClass);
        $entity = $repo->findOneBy($filter);

        if (null === $entity) {
            throw new \InvalidArgumentException(sprintf('%s "%s" does not exist', $shortName, $entityName));
        }

        $em->remove($entity);
        $em->flush();

        $output->writeln(sprintf('%s "%s" has been deleted.', $shortName, $entityName));
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
}
