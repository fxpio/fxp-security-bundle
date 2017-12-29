<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Bundle\SecurityBundle\Tests\DependencyInjection;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManager;
use Fxp\Component\Security\Authorization\Voter\ExpressionVoter;
use Fxp\Component\Security\Authorization\Voter\RoleSecurityIdentityVoter;
use Fxp\Component\Security\Role\OrganizationalRoleHierarchy;
use Fxp\Component\Security\SharingVisibilities;
use Fxp\Component\Security\Tests\Fixtures\Model\MockObject;
use Fxp\Component\Security\Tests\Fixtures\Model\MockPermission;
use Fxp\Component\Security\Tests\Fixtures\Model\MockRole;
use Fxp\Component\Security\Tests\Fixtures\Model\MockSharing;
use Sensio\Bundle\FrameworkExtraBundle\Templating\TemplateGuesser;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\Security\Core\Authentication\AuthenticationTrustResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;

/**
 * Security extension tests.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class FxpSecurityExtensionTest extends AbstractSecurityExtensionTest
{
    public function testExtensionExist()
    {
        $container = $this->createContainer([[
            'role_class' => MockRole::class,
            'permission_class' => MockPermission::class,
        ]]);
        $this->assertTrue($container->hasExtension('fxp_security'));
    }

    public function testObjectFilter()
    {
        $container = $this->createContainer([[
            'role_class' => MockRole::class,
            'permission_class' => MockPermission::class,
            'object_filter' => [
                'enabled' => true,
            ],
            'doctrine' => [
                'orm' => [
                    'object_filter_voter' => true,
                    'listeners' => [
                        'object_filter' => true,
                    ],
                ],
            ],
        ]], [], [
            'doctrine.orm.entity_manager' => new Definition(EntityManager::class),
        ]);

        $this->assertTrue($container->hasDefinition('fxp_security.object_filter'));
        $this->assertTrue($container->hasDefinition('fxp_security.object_filter.extension'));
        $this->assertTrue($container->hasDefinition('fxp_security.object_filter.voter.mixed'));

        $this->assertTrue($container->hasDefinition('fxp_security.object_filter.voter.doctrine_orm_collection'));
        $this->assertTrue($container->hasDefinition('fxp_security.object_filter.orm.listener'));
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage The "fxp_security.doctrine.orm.object_filter_voter" config require the "doctrine/orm" package
     */
    public function testOrmObjectFilterVoterWithoutDoctrine()
    {
        $this->createContainer([[
            'role_class' => MockRole::class,
            'permission_class' => MockPermission::class,
            'object_filter' => [
                'enabled' => true,
            ],
            'doctrine' => [
                'orm' => [
                    'object_filter_voter' => true,
                ],
            ],
        ]]);
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage The "fxp_security.doctrine.orm.listeners.object_filter" config require the "doctrine/orm" package
     */
    public function testOrmObjectFilterListenerWithoutDoctrine()
    {
        $this->createContainer([[
            'role_class' => MockRole::class,
            'permission_class' => MockPermission::class,
            'object_filter' => [
                'enabled' => true,
            ],
            'doctrine' => [
                'orm' => [
                    'listeners' => [
                        'object_filter' => true,
                    ],
                ],
            ],
        ]]);
    }

    public function testSecurityVoter()
    {
        $container = $this->createContainer([[
            'role_class' => MockRole::class,
            'permission_class' => MockPermission::class,
            'security_voter' => [
                'role_security_identity' => true,
                'groupable' => true,
            ],
        ]]);

        $this->assertTrue($container->hasDefinition('security.access.role_hierarchy_voter'));
        $this->assertTrue($container->hasDefinition('security.access.groupable_voter'));
        $this->assertTrue($container->hasDefinition('fxp_security.subscriber.security_identity.group'));

        $this->assertSame('%fxp_security.access.role_security_identity_voter.class%', $container->getDefinition('security.access.role_hierarchy_voter')->getClass());
        $this->assertSame(RoleSecurityIdentityVoter::class, $container->getParameter('fxp_security.access.role_security_identity_voter.class'));
    }

    public function testRoleHierarchy()
    {
        $container = $this->createContainer([[
            'role_class' => MockRole::class,
            'permission_class' => MockPermission::class,
            'role_hierarchy' => [
                'enabled' => true,
                'cache' => 'test_cache',
            ],
            'doctrine' => [
                'orm' => [
                    'listeners' => [
                        'role_hierarchy' => true,
                    ],
                ],
            ],
        ]], [], [
            'doctrine' => new Definition(Registry::class),
            'doctrine.orm.entity_manager' => new Definition(EntityManager::class),
        ]);

        $this->assertTrue($container->hasDefinition('security.role_hierarchy'));
        $this->assertTrue($container->hasAlias('fxp_security.role_hierarchy.cache'));
        $this->assertTrue($container->hasDefinition('fxp_security.role_hierarchy.cache.listener'));

        $def = $container->getDefinition('security.role_hierarchy');
        $this->assertSame('%security.role_hierarchy.class%', $def->getClass());
        $this->assertSame(OrganizationalRoleHierarchy::class, $container->getParameter('security.role_hierarchy.class'));
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage The "fxp_security.role_hierarchy" config require the "doctrine/doctrine-bundle" package
     */
    public function testRoleHierarchyWithoutDoctrineBundle()
    {
        $this->createContainer([[
            'role_class' => MockRole::class,
            'permission_class' => MockPermission::class,
            'role_hierarchy' => [
                'enabled' => true,
            ],
        ]]);
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage The "fxp_security.doctrine.orm.listeners.role_hierarchy" config require the "doctrine/orm" package
     */
    public function testOrmRoleHierarchyListenerWithoutDoctrine()
    {
        $this->createContainer([[
            'role_class' => MockRole::class,
            'permission_class' => MockPermission::class,
            'role_hierarchy' => [
                'enabled' => true,
            ],
            'doctrine' => [
                'orm' => [
                    'listeners' => [
                        'role_hierarchy' => true,
                    ],
                ],
            ],
        ]], [], [
            'doctrine' => new Definition(Registry::class),
        ]);
    }

    public function testOrganizationalContext()
    {
        $container = $this->createContainer([[
            'role_class' => MockRole::class,
            'permission_class' => MockPermission::class,
            'organizational_context' => [
                'enabled' => true,
            ],
        ]]);

        $this->assertTrue($container->hasDefinition('fxp_security.organizational_context.default'));
        $this->assertTrue($container->hasAlias('fxp_security.organizational_context'));
        $this->assertTrue($container->hasDefinition('security.access.organization_voter'));
        $this->assertTrue($container->hasDefinition('fxp_security.subscriber.security_identity.organization'));
    }

    public function testExpressionLanguage()
    {
        $container = $this->createContainer([[
            'role_class' => MockRole::class,
            'permission_class' => MockPermission::class,
            'organizational_context' => [
                'enabled' => true,
            ],
            'expression' => [
                'override_voter' => true,
                'functions' => [
                    'is_basic_auth' => true,
                    'is_granted' => true,
                    'is_organization' => true,
                ],
            ],
        ]], [], [
            'security.authorization_checker' => new Definition(AuthorizationChecker::class),
            'security.authentication.trust_resolver' => new Definition(AuthenticationTrustResolver::class),
        ]);

        $this->assertTrue($container->hasDefinition('fxp_security.expression.variable_storage'));
        $this->assertTrue($container->hasDefinition('security.access.expression_voter'));
        $this->assertTrue($container->hasDefinition('fxp_security.organizational_context.default'));
        $this->assertTrue($container->hasAlias('fxp_security.organizational_context'));

        $def = $container->getDefinition('security.access.expression_voter');
        $this->assertSame(ExpressionVoter::class, $def->getClass());

        $this->assertTrue($container->hasDefinition('fxp_security.expression.functions.is_basic_auth'));
        $this->assertTrue($container->hasDefinition('fxp_security.expression.functions.is_granted'));
        $this->assertTrue($container->hasDefinition('fxp_security.expression.functions.is_organization'));
    }

    /**
     * @expectedException \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @expectedExceptionMessage The service "fxp_security.expression.functions.is_granted" has a dependency on a non-existent service "security.authorization_checker"
     */
    public function testExpressionLanguageWitMissingDependenciesForIsGranted()
    {
        $this->createContainer([[
            'role_class' => MockRole::class,
            'permission_class' => MockPermission::class,
            'expression' => [
                'override_voter' => true,
                'functions' => [
                    'is_granted' => true,
                ],
            ],
        ]], [], [
            'security.authentication.trust_resolver' => new Definition(AuthenticationTrustResolver::class),
        ]);
    }

    /**
     * @expectedException \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @expectedExceptionMessage The service "fxp_security.expression.functions.is_organization" has a dependency on a non-existent service "fxp_security.organizational_context"
     */
    public function testExpressionLanguageWitMissingDependenciesForIsOrganization()
    {
        $this->createContainer([[
            'role_class' => MockRole::class,
            'permission_class' => MockPermission::class,
            'expression' => [
                'override_voter' => true,
                'functions' => [
                    'is_organization' => true,
                ],
            ],
        ]]);
    }

    public function testAnnotation()
    {
        $container = $this->createContainer([[
            'role_class' => MockRole::class,
            'permission_class' => MockPermission::class,
            'annotations' => [
                'security' => true,
            ],
        ]], [], [
            'sensio_framework_extra.view.guesser' => new Definition(TemplateGuesser::class),
        ]);

        $this->assertTrue($container->hasDefinition('fxp_security.subscriber.security_annotation'));
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage The "fxp_security.annotations.security" config require the "sensio/framework-extra-bundle" package
     */
    public function testAnnotationWitMissingDependencies()
    {
        $this->createContainer([[
            'role_class' => MockRole::class,
            'permission_class' => MockPermission::class,
            'annotations' => [
                'security' => true,
            ],
        ]]);
    }

    public function testOrmSharing()
    {
        $container = $this->createContainer([[
            'role_class' => MockRole::class,
            'permission_class' => MockPermission::class,
            'sharing_class' => MockSharing::class,
            'sharing' => [
                'enabled' => true,
            ],
            'doctrine' => [
                'orm' => [
                    'filters' => [
                        'sharing' => true,
                    ],
                ],
            ],
        ]], [], [
            'doctrine.orm.entity_manager' => new Definition(EntityManager::class),
        ]);

        $this->assertTrue($container->hasDefinition('fxp_security.orm.filter.subscriber.sharing'));
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage The "fxp_security.doctrine.orm.filter.sharing" config require the "doctrine/orm" package
     */
    public function testOrmSharingWithoutDoctrine()
    {
        $this->createContainer([[
            'role_class' => MockRole::class,
            'permission_class' => MockPermission::class,
            'sharing_class' => MockSharing::class,
            'sharing' => [
                'enabled' => true,
            ],
            'doctrine' => [
                'orm' => [
                    'filters' => [
                        'sharing' => true,
                    ],
                ],
            ],
        ]]);
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage The "fxp_security.sharing" config must be enabled
     */
    public function testOrmSharingDoctrineWithoutEnableSharing()
    {
        $this->createContainer([[
            'role_class' => MockRole::class,
            'permission_class' => MockPermission::class,
            'doctrine' => [
                'orm' => [
                    'filters' => [
                        'sharing' => true,
                    ],
                ],
            ],
        ]], [
            'doctrine.orm.entity_manager' => new Definition(EntityManager::class),
        ]);
    }

    public function testOrmSharingPrivateListener()
    {
        $container = $this->createContainer([[
            'role_class' => MockRole::class,
            'permission_class' => MockPermission::class,
            'sharing_class' => MockSharing::class,
            'sharing' => [
                'enabled' => true,
            ],
            'doctrine' => [
                'orm' => [
                    'filters' => [
                        'sharing' => true,
                    ],
                    'listeners' => [
                        'private_sharing' => true,
                    ],
                ],
            ],
        ]], [], [
            'doctrine.orm.entity_manager' => new Definition(EntityManager::class),
        ]);

        $this->assertTrue($container->hasDefinition('fxp_security.orm.filter.sharing.private_listener'));
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage The "fxp_security.doctrine.orm.filter.sharing" config require the "doctrine/orm" package
     */
    public function testOrmSharingPrivateListenerWithoutDoctrine()
    {
        $this->createContainer([[
            'role_class' => MockRole::class,
            'permission_class' => MockPermission::class,
            'sharing_class' => MockSharing::class,
            'sharing' => [
                'enabled' => true,
            ],
            'doctrine' => [
                'orm' => [
                    'filters' => [
                        'sharing' => true,
                    ],
                    'listeners' => [
                        'private_sharing' => true,
                    ],
                ],
            ],
        ]]);
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage The "fxp_security.doctrine.orm.filters.sharing" config must be enabled
     */
    public function testOrmSharingPrivateListenerWithoutEnableSharing()
    {
        $this->createContainer([[
            'role_class' => MockRole::class,
            'permission_class' => MockPermission::class,
            'doctrine' => [
                'orm' => [
                    'listeners' => [
                        'private_sharing' => true,
                    ],
                ],
            ],
        ]], [
            'doctrine.orm.entity_manager' => new Definition(EntityManager::class),
        ]);
    }

    public function testOrmSharingDelete()
    {
        $container = $this->createContainer([[
            'role_class' => MockRole::class,
            'permission_class' => MockPermission::class,
            'sharing_class' => MockSharing::class,
            'sharing' => [
                'enabled' => true,
            ],
            'doctrine' => [
                'orm' => [
                    'listeners' => [
                        'sharing_delete' => true,
                    ],
                ],
            ],
        ]], [], [
            'doctrine.orm.entity_manager' => new Definition(EntityManager::class),
        ]);

        $this->assertTrue($container->hasDefinition('fxp_security.orm.listener.sharing_delete'));
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage The "fxp_security.doctrine.orm.listeners.sharing_delete" config require the "doctrine/orm" package
     */
    public function testOrmSharingDeleteWithoutDoctrine()
    {
        $this->createContainer([[
            'role_class' => MockRole::class,
            'permission_class' => MockPermission::class,
            'sharing_class' => MockSharing::class,
            'sharing' => [
                'enabled' => true,
            ],
            'doctrine' => [
                'orm' => [
                    'listeners' => [
                        'sharing_delete' => true,
                    ],
                ],
            ],
        ]]);
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage The "fxp_security.sharing" config must be enabled
     */
    public function testOrmSharingDeleteDoctrineWithoutEnableSharing()
    {
        $this->createContainer([[
            'role_class' => MockRole::class,
            'permission_class' => MockPermission::class,
            'doctrine' => [
                'orm' => [
                    'listeners' => [
                        'sharing_delete' => true,
                    ],
                ],
            ],
        ]], [
            'doctrine.orm.entity_manager' => new Definition(EntityManager::class),
        ]);
    }

    public function testPermission()
    {
        $container = $this->createContainer([[
            'role_class' => MockRole::class,
            'permission_class' => MockPermission::class,
            'permissions' => [
                MockObject::class => true,
            ],
            'doctrine' => [
                'orm' => [
                    'listeners' => [
                        'permission_checker' => true,
                    ],
                ],
            ],
        ]], [], [
            'doctrine.orm.entity_manager' => new Definition(EntityManager::class),
        ]);

        $def = $container->getDefinition('fxp_security.permission_manager');
        $permConfigs = $def->getArgument(4);

        $this->assertInternalType('array', $permConfigs);
        $this->assertCount(1, $permConfigs);

        $this->assertTrue($container->hasDefinition('fxp_security.permission_checker.orm.listener'));
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage The "FooBar" permission class does not exist
     */
    public function testPermissionWithNonExistentClass()
    {
        $this->createContainer([[
            'role_class' => MockRole::class,
            'permission_class' => MockPermission::class,
            'permissions' => [
                'FooBar' => true,
            ],
        ]]);
    }

    public function testPermissionWithFields()
    {
        $container = $this->createContainer([[
            'role_class' => MockRole::class,
            'permission_class' => MockPermission::class,
            'permissions' => [
                MockObject::class => [
                    'fields' => [
                        'id' => null,
                        'name' => null,
                    ],
                ],
            ],
        ]]);

        $def = $container->getDefinition('fxp_security.permission_manager');
        $permConfigs = $def->getArgument(4);

        $this->assertInternalType('array', $permConfigs);
        $this->assertCount(1, $permConfigs);
    }

    public function testPermissionWithDefaultFields()
    {
        $container = $this->createContainer([[
            'role_class' => MockRole::class,
            'permission_class' => MockPermission::class,
            'default_permissions' => [
                'fields' => [
                    'id' => ['read'],
                ],
            ],
            'permissions' => [
                MockObject::class => [
                    'fields' => [
                        'name' => null,
                    ],
                ],
            ],
        ]]);

        $def = $container->getDefinition('fxp_security.permission_manager');
        $permConfigs = $def->getArgument(4);

        $this->assertInternalType('array', $permConfigs);
        $this->assertCount(1, $permConfigs);
    }

    public function testMasterMappingPermissionWithDefaultMapping()
    {
        $container = $this->createContainer([[
            'role_class' => MockRole::class,
            'permission_class' => MockPermission::class,
            'default_permissions' => [
                'master_mapping_permissions' => [
                    'view' => 'read',
                    'update' => 'edit',
                    'create' => 'edit',
                    'delete' => 'edit',
                ],
            ],
            'permissions' => [
                MockObject::class => [
                    'master' => 'name',
                ],
            ],
        ]]);

        $def = $container->getDefinition('fxp_security.permission_manager');
        $permConfigs = $def->getArgument(4);

        $this->assertInternalType('array', $permConfigs);
        $this->assertCount(1, $permConfigs);
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage The permission field "foo" does not exist in "Fxp\Component\Security\Tests\Fixtures\Model\MockObject" class
     */
    public function testPermissionWithNonExistentField()
    {
        $this->createContainer([[
            'role_class' => MockRole::class,
            'permission_class' => MockPermission::class,
            'permissions' => [
                MockObject::class => [
                    'fields' => [
                        'foo' => null,
                    ],
                ],
            ],
        ]]);
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage The "fxp_security.doctrine.orm.listeners.permission_checker" config require the "doctrine/orm" package
     */
    public function testOrmPermissionCheckerListenerWithoutDoctrine()
    {
        $this->createContainer([[
            'role_class' => MockRole::class,
            'permission_class' => MockPermission::class,
            'permissions' => [
                MockObject::class => [],
            ],
            'doctrine' => [
                'orm' => [
                    'listeners' => [
                        'permission_checker' => true,
                    ],
                ],
            ],
        ]]);
    }

    public function testSharing()
    {
        $container = $this->createContainer([[
            'role_class' => MockRole::class,
            'permission_class' => MockPermission::class,
            'sharing_class' => MockSharing::class,
            'sharing' => [
                'enabled' => true,
                'identity_types' => [
                    MockRole::class => [
                        'alias' => 'foo',
                        'roleable' => true,
                        'permissible' => true,
                    ],
                ],
            ],
        ]]);

        $def = $container->getDefinition('fxp_security.sharing_manager');
        $identityConfigs = $def->getArgument(2);

        $this->assertInternalType('array', $identityConfigs);
        $this->assertCount(1, $identityConfigs);
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage The "fxp_security.sharing_class" config must be configured with a valid class
     */
    public function testSharingWithoutSharingClass()
    {
        $this->createContainer([[
            'role_class' => MockRole::class,
            'permission_class' => MockPermission::class,
            'sharing' => [
                'enabled' => true,
            ],
        ]]);
    }

    public function testSharingWithDirectIdentityAlias()
    {
        $container = $this->createContainer([[
            'role_class' => MockRole::class,
            'permission_class' => MockPermission::class,
            'sharing_class' => MockSharing::class,
            'sharing' => [
                'enabled' => true,
                'identity_types' => [
                    MockRole::class => 'foo',
                ],
            ],
        ]]);

        $def = $container->getDefinition('fxp_security.sharing_manager');
        $identityConfigs = $def->getArgument(2);

        $this->assertInternalType('array', $identityConfigs);
        $this->assertCount(1, $identityConfigs);
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage The "FooBar" sharing identity class does not exist
     */
    public function testSharingWithNonExistentIdentityClass()
    {
        $this->createContainer([[
            'role_class' => MockRole::class,
            'permission_class' => MockPermission::class,
            'sharing_class' => MockSharing::class,
            'sharing' => [
                'enabled' => true,
                'identity_types' => [
                    'FooBar' => [
                        'alias' => 'foo',
                        'roleable' => true,
                        'permissible' => true,
                    ],
                ],
            ],
        ]]);
    }

    public function testSharingWithSubject()
    {
        $container = $this->createContainer([[
            'role_class' => MockRole::class,
            'permission_class' => MockPermission::class,
            'sharing_class' => MockSharing::class,
            'sharing' => [
                'enabled' => true,
                'subjects' => [
                    MockObject::class => SharingVisibilities::TYPE_PRIVATE,
                ],
            ],
        ]]);

        $def = $container->getDefinition('fxp_security.sharing_manager');
        $subjectConfigs = $def->getArgument(1);

        $this->assertInternalType('array', $subjectConfigs);
        $this->assertCount(1, $subjectConfigs);
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage The "FooBar" sharing subject class does not exist
     */
    public function testSharingWithNonExistentSubjectClass()
    {
        $this->createContainer([[
            'role_class' => MockRole::class,
            'permission_class' => MockPermission::class,
            'sharing_class' => MockSharing::class,
            'sharing' => [
                'enabled' => true,
                'subjects' => [
                    'FooBar' => [
                        'visibility' => SharingVisibilities::TYPE_PRIVATE,
                    ],
                ],
            ],
        ]]);
    }
}
