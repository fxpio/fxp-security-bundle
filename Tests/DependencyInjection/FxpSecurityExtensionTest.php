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
use Fxp\Component\Security\Tests\Fixtures\Model\MockRole;
use Sensio\Bundle\FrameworkExtraBundle\Templating\TemplateGuesser;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\Security\Core\Authentication\AuthenticationTrustResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;

/**
 * Security extension tests.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 *
 * @internal
 * @coversNothing
 */
final class FxpSecurityExtensionTest extends AbstractSecurityExtensionTest
{
    public function testExtensionExist(): void
    {
        $container = $this->createContainer([[]]);
        $this->assertTrue($container->hasExtension('fxp_security'));
    }

    public function testObjectFilter(): void
    {
        $container = $this->createContainer([[
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

    public function testOrmObjectFilterVoterWithoutDoctrine(): void
    {
        $this->expectException(\Symfony\Component\Config\Definition\Exception\InvalidConfigurationException::class);
        $this->expectExceptionMessage('The "fxp_security.doctrine.orm.object_filter_voter" config require the "doctrine/orm" package');

        $this->createContainer([[
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

    public function testOrmObjectFilterListenerWithoutDoctrine(): void
    {
        $this->expectException(\Symfony\Component\Config\Definition\Exception\InvalidConfigurationException::class);
        $this->expectExceptionMessage('The "fxp_security.doctrine.orm.listeners.object_filter" config require the "doctrine/orm" package');

        $this->createContainer([[
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

    public function testSecurityVoter(): void
    {
        $container = $this->createContainer([[
            'security_voter' => [
                'role_security_identity' => true,
                'groupable' => true,
            ],
        ]]);

        $this->assertTrue($container->hasDefinition('security.access.role_hierarchy_voter'));
        $this->assertTrue($container->hasDefinition('security.access.groupable_voter'));
        $this->assertTrue($container->hasDefinition('fxp_security.subscriber.security_identity.group'));

        $this->assertSame(RoleSecurityIdentityVoter::class, $container->getDefinition('security.access.role_hierarchy_voter')->getClass());
    }

    public function testRoleHierarchy(): void
    {
        $container = $this->createContainer([[
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
        $this->assertSame(OrganizationalRoleHierarchy::class, $def->getClass());
    }

    public function testRoleHierarchyWithoutDoctrineBundle(): void
    {
        $this->expectException(\Symfony\Component\Config\Definition\Exception\InvalidConfigurationException::class);
        $this->expectExceptionMessage('The "fxp_security.role_hierarchy" config require the "doctrine/doctrine-bundle" package');

        $this->createContainer([[
            'role_hierarchy' => [
                'enabled' => true,
            ],
        ]]);
    }

    public function testOrmRoleHierarchyListenerWithoutDoctrine(): void
    {
        $this->expectException(\Symfony\Component\Config\Definition\Exception\InvalidConfigurationException::class);
        $this->expectExceptionMessage('The "fxp_security.doctrine.orm.listeners.role_hierarchy" config require the "doctrine/orm" package');

        $this->createContainer([[
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

    public function testOrganizationalContext(): void
    {
        $container = $this->createContainer([[
            'organizational_context' => [
                'enabled' => true,
            ],
        ]]);

        $this->assertTrue($container->hasDefinition('fxp_security.organizational_context.default'));
        $this->assertTrue($container->hasAlias('fxp_security.organizational_context'));
        $this->assertTrue($container->hasDefinition('security.access.organization_voter'));
        $this->assertTrue($container->hasDefinition('fxp_security.subscriber.security_identity.organization'));
    }

    public function testExpressionLanguage(): void
    {
        $container = $this->createContainer([[
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

    public function testExpressionLanguageWitMissingDependenciesForIsGranted(): void
    {
        $this->expectException(\Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException::class);
        $this->expectExceptionMessage('The service "fxp_security.expression.functions.is_granted" has a dependency on a non-existent service "security.authorization_checker"');

        $this->createContainer([[
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

    public function testExpressionLanguageWitMissingDependenciesForIsOrganization(): void
    {
        $this->expectException(\Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException::class);
        $this->expectExceptionMessage('The service "fxp_security.expression.functions.is_organization" has a dependency on a non-existent service "fxp_security.organizational_context"');

        $this->createContainer([[
            'expression' => [
                'override_voter' => true,
                'functions' => [
                    'is_organization' => true,
                ],
            ],
        ]]);
    }

    public function testAnnotation(): void
    {
        $container = $this->createContainer([[
            'annotations' => [
                'security' => true,
            ],
        ]], [], [
            'sensio_framework_extra.view.guesser' => new Definition(TemplateGuesser::class),
        ]);

        $this->assertTrue($container->hasDefinition('fxp_security.subscriber.security_annotation'));
    }

    public function testAnnotationWitMissingDependencies(): void
    {
        $this->expectException(\Symfony\Component\Config\Definition\Exception\InvalidConfigurationException::class);
        $this->expectExceptionMessage('The "fxp_security.annotations.security" config require the "sensio/framework-extra-bundle" package');

        $this->createContainer([[
            'annotations' => [
                'security' => true,
            ],
        ]]);
    }

    public function testOrmSharing(): void
    {
        $container = $this->createContainer([[
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

    public function testOrmSharingWithoutDoctrine(): void
    {
        $this->expectException(\Symfony\Component\Config\Definition\Exception\InvalidConfigurationException::class);
        $this->expectExceptionMessage('The "fxp_security.doctrine.orm.filter.sharing" config require the "doctrine/orm" package');

        $this->createContainer([[
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

    public function testOrmSharingDoctrineWithoutEnableSharing(): void
    {
        $this->expectException(\Symfony\Component\Config\Definition\Exception\InvalidConfigurationException::class);
        $this->expectExceptionMessage('The "fxp_security.sharing" config must be enabled');

        $this->createContainer([[
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

    public function testOrmSharingPrivateListener(): void
    {
        $container = $this->createContainer([[
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

    public function testOrmSharingPrivateListenerWithoutDoctrine(): void
    {
        $this->expectException(\Symfony\Component\Config\Definition\Exception\InvalidConfigurationException::class);
        $this->expectExceptionMessage('The "fxp_security.doctrine.orm.filter.sharing" config require the "doctrine/orm" package');

        $this->createContainer([[
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

    public function testOrmSharingPrivateListenerWithoutEnableSharing(): void
    {
        $this->expectException(\Symfony\Component\Config\Definition\Exception\InvalidConfigurationException::class);
        $this->expectExceptionMessage('The "fxp_security.doctrine.orm.filters.sharing" config must be enabled');

        $this->createContainer([[
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

    public function testOrmSharingDelete(): void
    {
        $container = $this->createContainer([[
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

    public function testOrmSharingDeleteWithoutDoctrine(): void
    {
        $this->expectException(\Symfony\Component\Config\Definition\Exception\InvalidConfigurationException::class);
        $this->expectExceptionMessage('The "fxp_security.doctrine.orm.listeners.sharing_delete" config require the "doctrine/orm" package');

        $this->createContainer([[
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

    public function testOrmSharingDeleteDoctrineWithoutEnableSharing(): void
    {
        $this->expectException(\Symfony\Component\Config\Definition\Exception\InvalidConfigurationException::class);
        $this->expectExceptionMessage('The "fxp_security.sharing" config must be enabled');

        $this->createContainer([[
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

    public function testPermission(): void
    {
        $container = $this->createContainer([[
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

    public function testPermissionWithNonExistentClass(): void
    {
        $this->expectException(\Symfony\Component\Config\Definition\Exception\InvalidConfigurationException::class);
        $this->expectExceptionMessage('The "FooBar" permission class does not exist');

        $this->createContainer([[
            'permissions' => [
                'FooBar' => true,
            ],
        ]]);
    }

    public function testPermissionWithFields(): void
    {
        $container = $this->createContainer([[
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

    public function testPermissionWithDefaultFields(): void
    {
        $container = $this->createContainer([[
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

    public function testMasterMappingPermissionWithDefaultMapping(): void
    {
        $container = $this->createContainer([[
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

    public function testPermissionWithNonExistentField(): void
    {
        $this->expectException(\Symfony\Component\Config\Definition\Exception\InvalidConfigurationException::class);
        $this->expectExceptionMessage('The permission field "foo" does not exist in "Fxp\\Component\\Security\\Tests\\Fixtures\\Model\\MockObject" class');

        $this->createContainer([[
            'permissions' => [
                MockObject::class => [
                    'fields' => [
                        'foo' => null,
                    ],
                ],
            ],
        ]]);
    }

    public function testOrmPermissionCheckerListenerWithoutDoctrine(): void
    {
        $this->expectException(\Symfony\Component\Config\Definition\Exception\InvalidConfigurationException::class);
        $this->expectExceptionMessage('The "fxp_security.doctrine.orm.listeners.permission_checker" config require the "doctrine/orm" package');

        $this->createContainer([[
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

    public function testSharing(): void
    {
        $container = $this->createContainer([[
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

    public function testSharingWithDirectIdentityAlias(): void
    {
        $container = $this->createContainer([[
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

    public function testSharingWithNonExistentIdentityClass(): void
    {
        $this->expectException(\Symfony\Component\Config\Definition\Exception\InvalidConfigurationException::class);
        $this->expectExceptionMessage('The "FooBar" sharing identity class does not exist');

        $this->createContainer([[
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

    public function testSharingWithSubject(): void
    {
        $container = $this->createContainer([[
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

    public function testSharingWithNonExistentSubjectClass(): void
    {
        $this->expectException(\Symfony\Component\Config\Definition\Exception\InvalidConfigurationException::class);
        $this->expectExceptionMessage('The "FooBar" sharing subject class does not exist');

        $this->createContainer([[
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
