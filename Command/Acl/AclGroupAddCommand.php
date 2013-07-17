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
use Sonatra\Bundle\SecurityBundle\Exception\SecurityException;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class AclGroupAddCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('acl:group:add')
        ->setDescription('Creates a new Group')
        ->setDefinition(array(
                new InputArgument('group-name', InputArgument::REQUIRED, 'The group name to create'),
                new InputOption('field',null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                                'Specifies the fields values, used as follow : --field="myFieldName=\'my value\'" or --field="myFieldName:\'my value\'" both can be accepted.')
         ));
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $groupClass = str_replace('/', '\\', $this->getContainer()->getParameter('sonatra_security.group_class'));
        $groupName = $input->getArgument('group-name');

        try {
            // Gets the field option
            $fields = $input->getOption('field');

            // Persists the group with the profile option and with the groupname.
            $em = $this->getContainer()->get('doctrine.orm.entity_manager');
            $group = new $groupClass();
            $group->setName($groupName);

            // Processes the fields
            if (!empty($fields)) {

                // Get columns and associations here
                $classMetadata = $em->getClassMetadata($groupClass);
                $fieldList = $classMetadata->getColumnNames();
                $realFieldNamesList = array();

                foreach ($fieldList as $field) {
                    $realFieldNamesList[] = $classMetadata->getFieldForColumn($field);
                }

                $associationList = $classMetadata->getAssociationNames();
                $realAssociationsNamesList = array();

                foreach ($associationList as $association) {
                    $mapping = $classMetadata->getAssociationMapping($association);
                    $realAssociationsNamesList[] = $mapping['fieldName'];
                }

                foreach ($fields as $field) {
                    $equalsPos = strpos($field, "=");
                    if (!$equalsPos) {
                        $semiColonPos = strpos ($field, ":");
                        if (!$semiColonPos) {
                            throw new SecurityException('The field '.$field.' was misformatted or doesn\'t contain an = or : character.');

                        } else {
                            // The : character was found, does the spilt
                            $splited = explode(":", $field, 2);
                        }

                    } else {
                        // The = character was found, does the split
                        $splited = explode("=", $field, 2);
                    }

                    $fieldName = $splited[0];
                    $fieldValue = trim($splited[1], "'");
                    $fieldValue = trim($fieldValue, '"');

                    if ('' === $fieldValue) {
                        $fieldValue = null;
                    }

                    // Check the field existence here in columns
                    if ((in_array($fieldName, $realFieldNamesList))) {
                        // Field exists as column in the object
                        $setterMethodName = "set".ucfirst($fieldName);

                        try {
                            $reflectionRoleClass = new \ReflectionClass($groupClass);
                            $setterMethod = $reflectionRoleClass->getMethod($setterMethodName);

                        } catch (\Exception $e) {
                            throw new SecurityException('The setter method "'.$setterMethodName.'" that should be used for property "'.$fieldName.'" seems not to exist. Please check your spelling in the command option or in your implementation class.');
                        }

                        $group->$setterMethodName($fieldValue);

                    } else {
                        // Here we are in a case of an association
                        if ((in_array($fieldName, $realAssociationsNamesList))) {
                            $mapping = $classMetadata->getAssociationMapping($fieldName);
                            $targetEntity = $mapping['targetEntity'];
                            $targetRepo = $em->getRepository($targetEntity);
                            $targetEntity = $targetRepo->findBy(array('id' => $fieldValue));

                            if (null == $targetEntity) {
                                throw new SecurityException('The specified mapped field '.$fieldName.' couldn\'t be found with the ID '.$fieldValue.'. Aborting.' );
                            }

                            $targetEntity = $targetEntity[0];
                            $setterMethodName = "set".ucfirst($fieldName);

                            try {
                                $reflectionRoleClass = new \ReflectionClass($groupClass);
                                $setterMethod = $reflectionRoleClass->getMethod($setterMethodName);

                            } catch (\Exception $e) {
                                throw new SecurityException('The setter method "'.$setterMethodName.'" that should be used for property "'.$fieldName.'" seems not to exist. Please check your spelling in the command option or in your implementation class.');
                            }

                            $group->$setterMethodName($targetEntity);

                        } else {
                            throw new SecurityException('The field '.$fieldName.' seems not to exist in your group class.');
                        }
                    }
                }
            }

            $em->persist($group);
            $em->flush();

            $output->writeln(array('', "The group <info>$groupName</info> was successfully created"));

        } catch (\Exception $e) {
            $prevError = $e->getPrevious();
            $exCode = isset($previous) ? $prevError->getCode() : $e->getCode();

            if (23000 == $exCode) {
                throw new \Exception("The groupname $groupName seems to already exist");
            }

            // Unknown error.
            throw $e;
        }
    }
}
