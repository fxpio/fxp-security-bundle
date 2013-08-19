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
use Sonatra\Bundle\SecurityBundle\Exception\InvalidArgumentException;
use Sonatra\Bundle\SecurityBundle\Exception\LogicException;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
abstract class CreateCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setDefinition(array(
                new InputArgument('name', InputArgument::OPTIONAL, 'The name'),
                new InputOption('field',null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                                'Specifies the fields values, used as follow : --field="myFieldName=\'my value\'" or --field="myFieldName:\'my value\'" both can be accepted.')
         ));
    }

    /**
     * Execution of delete action
     *
     * @param string $entityClass The entity class name
     * @param string $entityName  The entity name
     * @param array  $fields      The field values
     *
     * @throws \InvalidArgumentException When the field is misformatted
     * @throws \InvalidArgumentException When the setter method of class does not exist
     * @throws \InvalidArgumentException When the specified mapped field could not be found
     * @throws \InvalidArgumentException When the setter method that should be user for property seems not to exist
     * @throws \InvalidArgumentException When the field seems not exist in class
     * @throws \InvalidArgumentException When the validator has errors
     */
    protected function doExecute(OutputInterface $output, $entityClass, $entityName, array $fields)
    {
        $entityClass = str_replace('/', '\\', $entityClass);
        $shortName = substr($entityClass, strrpos($entityClass, '\\') + 1);
        $em = $this->getContainer()->get('doctrine')->getManagerForClass($entityClass);
        $repo = $em->getRepository($entityClass);
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $entity = new $entityClass($entityName);

        // Processes the fields
        if (!empty($fields)) {
            // Get columns and associations here
            $classMetadata = $em->getClassMetadata($entityClass);
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
                        throw new InvalidArgumentException(sprintf('The field "%s" was misformatted or doesn\'t contain an = or : character.', $field));

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
                        $reflectionRoleClass = new \ReflectionClass($entityClass);
                        $setterMethod = $reflectionRoleClass->getMethod($setterMethodName);

                    } catch (\Exception $e) {
                        throw new InvalidArgumentException(sprintf('The setter method "%s" that should be used for property "%s" seems not to exist. Please check your spelling in the command option or in your implementation class.', $setterMethodName, $fieldName));
                    }

                    $entity->$setterMethodName($fieldValue);

                } else {
                    // Here we are in a case of an association
                    if ((in_array($fieldName, $realAssociationsNamesList))) {
                        $mapping = $classMetadata->getAssociationMapping($fieldName);
                        $targetEntity = $mapping['targetEntity'];
                        $targetRepo = $em->getRepository($targetEntity);
                        $targetEntity = $targetRepo->findBy(array('id' => $fieldValue));

                        if (null == $targetEntity) {
                            throw new InvalidArgumentException(sprintf('The specified mapped field "%s" couldn\'t be found with the Id "%s".', $fieldName, $fieldValue) );
                        }

                        $targetEntity = $targetEntity[0];
                        $setterMethodName = "set".ucfirst($fieldName);

                        try {
                            $reflectionRoleClass = new \ReflectionClass($entityClass);
                            $setterMethod = $reflectionRoleClass->getMethod($setterMethodName);

                        } catch (\Exception $e) {
                            throw new InvalidArgumentException(sprintf('The setter method "%s" that should be used for property "%s" seems not to exist. Please check your spelling in the command option or in your implementation class.', $setterMethodName, $fieldName));
                        }

                        $entity->$setterMethodName($targetEntity);

                    } else {
                        throw new InvalidArgumentException(sprintf('The field "%s" seems not to exist in your "%s" class.', $fieldName, $shortName));
                    }
                }
            }
        }

        $errorList = $this->getContainer()->get('validator')->validate($entity);

        if (count($errorList) > 0) {
            $msg = sprintf('Validation errors for "%s":%s', get_class($entity), PHP_EOL);

            foreach ($errorList as $error) {
                $msg = sprintf('%s%s: %s', PHP_EOL, $error->getPropertyPath(), $error->getMessage());
            }

            throw new LogicException($msg);
        }

        $em->persist($entity);
        $em->flush();

        $output->writeln(sprintf('%s "%s" has been created.', $shortName, $entityName));
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
                            throw new LogicException('Name can not be empty');
                        }

                        return $name;
                    }
            );

            $input->setArgument('name', $name);
        }
    }
}
