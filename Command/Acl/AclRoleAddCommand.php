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
class AclRoleAddCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('acl:role:add')
        ->setDescription('Creates a new Role')
        ->setDefinition(array(
                new InputArgument('roleName', InputArgument::REQUIRED, 'The role name to create'),
                new InputOption('field',null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                                'Specifies the fields values, used as follow : --field="myFieldName=\'my value\'" or --field="myFieldName:\'my value\'" both can be accepted.')
         ));
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $roleClass = str_replace('/', '\\', $this->getContainer()->getParameter('sonatra_security.role_class'));
        $roleName = $input->getArgument('roleName');

        try {
            // Gets the field option
            $fields = $input->getOption('field');

            // Persists the role with the profile option and with the rolename.
            $em = $this->getContainer()->get('doctrine.orm.entity_manager');
            $role = new $roleClass();
            $role->setName($roleName);

            // Processes the fields
            if (!empty($fields)) {

                // Get columns and associations here
                $classMetadata = $em->getClassMetadata($roleClass);
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
                            $reflectionRoleClass = new \ReflectionClass($roleClass);
                            $setterMethod = $reflectionRoleClass->getMethod($setterMethodName);

                        } catch (\Exception $e) {
                            throw new SecurityException('The setter method "'.$setterMethodName.'" that should be used for property "'.$fieldName.'" seems not to exist. Please check your spelling in the command option or in your implementation class.');
                        }

                        $role->$setterMethodName($fieldValue);

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
                                $reflectionRoleClass = new \ReflectionClass($roleClass);
                                $setterMethod = $reflectionRoleClass->getMethod($setterMethodName);

                            } catch (\Exception $e) {
                                throw new SecurityException('The setter method "'.$setterMethodName.'" that should be used for property "'.$fieldName.'" seems not to exist. Please check your spelling in the command option or in your implementation class.');
                            }

                            $role->$setterMethodName($targetEntity);

                        } else {
                            throw new SecurityException('The field '.$fieldName.' seems not to exist in your role class.');
                        }
                    }
                }
            }

            $em->persist($role);
            $em->flush();

            $output->writeln(array('', "The role <info>$roleName</info> was successfully created"));

        } catch (\Exception $e) {
            $prevError = $e->getPrevious();
            $exCode = isset($previous) ? $prevError->getCode() : $e->getCode();

            if (23000 == $exCode) {
                throw new \Exception("The rolename $roleName seems to already exist");
            }

            // Unknown error.
            throw $e;
        }
    }
}
