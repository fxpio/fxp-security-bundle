<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\SecurityBundle\Tests\DependencyInjection;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Templating\TemplateGuesser;
use Sonatra\Component\Security\Authorization\Voter\ExpressionVoter;
use Sonatra\Component\Security\Authorization\Voter\RoleSecurityIdentityVoter;
use Sonatra\Component\Security\Role\OrganizationalRoleHierarchy;
use Sonatra\Component\Security\Tests\Fixtures\Model\MockObject;
use Sonatra\Component\Security\Tests\Fixtures\Model\MockPermission;
use Sonatra\Component\Security\Tests\Fixtures\Model\MockRole;
use Sonatra\Component\Security\Tests\Fixtures\Model\MockSharing;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\Security\Core\Authentication\AuthenticationTrustResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;

/**
 * Security extension tests.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class SonatraSecurityExtensionTest extends AbstractSecurityExtensionTest
{
    public function testExtensionExist()
    {
        $container = $this->createContainer(array(array(
            'role_class' => MockRole::class,
            'permission_class' => MockPermission::class,
        )));
        $this->assertTrue($container->hasExtension('sonatra_security'));
    }

    public function testObjectFilter()
    {
        $container = $this->createContainer(array(array(
            'role_class' => MockRole::class,
            'permission_class' => MockPermission::class,
            'object_filter' => array(
                'enabled' => true,
            ),
            'doctrine' => array(
                'orm' => array(
                    'object_filter_voter' => true,
                    'listeners' => array(
                        'object_filter' => true,
                    ),
                ),
            ),
        )), array(
            'doctrine.orm.entity_manager.class' => EntityManager::class,
        ));

        $this->assertTrue($container->hasDefinition('sonatra_security.object_filter'));
        $this->assertTrue($container->hasDefinition('sonatra_security.object_filter.extension'));
        $this->assertTrue($container->hasDefinition('sonatra_security.object_filter.voter.mixed'));

        $this->assertTrue($container->hasDefinition('sonatra_security.object_filter.voter.doctrine_orm_collection'));
        $this->assertTrue($container->hasDefinition('sonatra_security.object_filter.orm.listener'));
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage The "sonatra_security.doctrine.orm.object_filter_voter" config require the "doctrine/orm" package
     */
    public function testOrmObjectFilterVoterWithoutDoctrine()
    {
        $this->createContainer(array(array(
            'role_class' => MockRole::class,
            'permission_class' => MockPermission::class,
            'object_filter' => array(
                'enabled' => true,
            ),
            'doctrine' => array(
                'orm' => array(
                    'object_filter_voter' => true,
                ),
            ),
        )));
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage The "sonatra_security.doctrine.orm.listeners.object_filter" config require the "doctrine/orm" package
     */
    public function testOrmObjectFilterListenerWithoutDoctrine()
    {
        $this->createContainer(array(array(
            'role_class' => MockRole::class,
            'permission_class' => MockPermission::class,
            'object_filter' => array(
                'enabled' => true,
            ),
            'doctrine' => array(
                'orm' => array(
                    'listeners' => array(
                        'object_filter' => true,
                    ),
                ),
            ),
        )));
    }

    public function testSecurityVoter()
    {
        $container = $this->createContainer(array(array(
            'role_class' => MockRole::class,
            'permission_class' => MockPermission::class,
            'security_voter' => array(
                'role_security_identity' => true,
                'groupable' => true,
            ),
        )));

        $this->assertTrue($container->hasDefinition('security.access.role_hierarchy_voter'));
        $this->assertTrue($container->hasDefinition('security.access.groupable_voter'));
        $this->assertTrue($container->hasDefinition('sonatra_security.subscriber.security_identity.group'));

        $this->assertSame('%sonatra_security.access.role_security_identity_voter.class%', $container->getDefinition('security.access.role_hierarchy_voter')->getClass());
        $this->assertSame(RoleSecurityIdentityVoter::class, $container->getParameter('sonatra_security.access.role_security_identity_voter.class'));
    }

    public function testRoleHierarchy()
    {
        $container = $this->createContainer(array(array(
            'role_class' => MockRole::class,
            'permission_class' => MockPermission::class,
            'role_hierarchy' => array(
                'enabled' => true,
                'cache' => 'test_cache',
            ),
            'doctrine' => array(
                'orm' => array(
                    'listeners' => array(
                        'role_hierarchy' => true,
                    ),
                ),
            ),
        )), array(
            'doctrine.class' => Registry::class,
            'doctrine.orm.entity_manager.class' => EntityManager::class,
        ));

        $this->assertTrue($container->hasDefinition('security.role_hierarchy'));
        $this->assertTrue($container->hasAlias('sonatra_security.role_hierarchy.cache'));
        $this->assertTrue($container->hasDefinition('sonatra_security.role_hierarchy.cache.listener'));

        $def = $container->getDefinition('security.role_hierarchy');
        $this->assertSame('%security.role_hierarchy.class%', $def->getClass());
        $this->assertSame(OrganizationalRoleHierarchy::class, $container->getParameter('security.role_hierarchy.class'));
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage The "sonatra_security.role_hierarchy" config require the "doctrine/doctrine-bundle" package
     */
    public function testRoleHierarchyWithoutDoctrineBundle()
    {
        $this->createContainer(array(array(
            'role_class' => MockRole::class,
            'permission_class' => MockPermission::class,
            'role_hierarchy' => array(
                'enabled' => true,
            ),
        )));
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage The "sonatra_security.doctrine.orm.listeners.role_hierarchy" config require the "doctrine/orm" package
     */
    public function testOrmRoleHierarchyListenerWithoutDoctrine()
    {
        $this->createContainer(array(array(
            'role_class' => MockRole::class,
            'permission_class' => MockPermission::class,
            'role_hierarchy' => array(
                'enabled' => true,
            ),
            'doctrine' => array(
                'orm' => array(
                    'listeners' => array(
                        'role_hierarchy' => true,
                    ),
                ),
            ),
        )), array(
            'doctrine.class' => Registry::class,
        ));
    }

    public function testOrganizationalContext()
    {
        $container = $this->createContainer(array(array(
            'role_class' => MockRole::class,
            'permission_class' => MockPermission::class,
            'organizational_context' => array(
                'enabled' => true,
            ),
        )));

        $this->assertTrue($container->hasDefinition('sonatra_security.organizational_context.default'));
        $this->assertTrue($container->hasDefinition('security.access.organization_voter'));
        $this->assertTrue($container->hasDefinition('sonatra_security.subscriber.security_identity.organization'));
    }

    public function testExpressionLanguage()
    {
        $container = $this->createContainer(array(array(
            'role_class' => MockRole::class,
            'permission_class' => MockPermission::class,
            'organizational_context' => array(
                'enabled' => true,
            ),
            'expression' => array(
                'override_voter' => true,
                'functions' => array(
                    'is_basic_auth' => true,
                    'has_org_role' => true,
                    'is_granted' => true,
                ),
            ),
        )), array(), array(
            'security.authorization_checker' => new Definition(AuthorizationChecker::class),
            'security.authentication.trust_resolver' => new Definition(AuthenticationTrustResolver::class),
        ));

        $this->assertTrue($container->hasDefinition('sonatra_security.expression.variable_storage'));
        $this->assertTrue($container->hasDefinition('security.access.expression_voter'));

        $def = $container->getDefinition('security.access.expression_voter');
        $this->assertSame(ExpressionVoter::class, $def->getClass());

        $this->assertTrue($container->hasDefinition('sonatra_security.expression.functions.is_basic_auth'));
        $this->assertTrue($container->hasDefinition('sonatra_security.expression.functions.has_org_role'));
        $this->assertTrue($container->hasDefinition('sonatra_security.expression.functions.is_granted'));
    }

    /**
     * @expectedException \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @expectedExceptionMessage The service "sonatra_security.expression.functions.has_org_role" has a dependency on a non-existent service "sonatra_security.organizational_role"
     */
    public function testExpressionLanguageWitMissingDependencies()
    {
        $this->createContainer(array(array(
            'role_class' => MockRole::class,
            'permission_class' => MockPermission::class,
            'expression' => array(
                'override_voter' => true,
                'functions' => array(
                    'has_org_role' => true,
                ),
            ),
        )), array(), array(
            'security.authentication.trust_resolver' => new Definition(AuthenticationTrustResolver::class),
        ));
    }

    /**
     * @expectedException \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @expectedExceptionMessage The service "sonatra_security.expression.functions.is_granted" has a dependency on a non-existent service "security.authorization_checker"
     */
    public function testExpressionLanguageWitMissingDependenciesForIsGranted()
    {
        $this->createContainer(array(array(
            'role_class' => MockRole::class,
            'permission_class' => MockPermission::class,
            'expression' => array(
                'override_voter' => true,
                'functions' => array(
                    'is_granted' => true,
                ),
            ),
        )), array(), array(
            'security.authentication.trust_resolver' => new Definition(AuthenticationTrustResolver::class),
        ));
    }

    public function testAnnotation()
    {
        $container = $this->createContainer(array(array(
            'role_class' => MockRole::class,
            'permission_class' => MockPermission::class,
            'annotations' => array(
                'security' => true,
            ),
        )), array(
            'sensio_framework_extra.view.guesser.class' => TemplateGuesser::class,
        ));

        $this->assertTrue($container->hasDefinition('sonatra_security.subscriber.security_annotation'));
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage The "sonatra_security.annotations.security" config require the "sensio/framework-extra-bundle" package
     */
    public function testAnnotationWitMissingDependencies()
    {
        $this->createContainer(array(array(
            'role_class' => MockRole::class,
            'permission_class' => MockPermission::class,
            'annotations' => array(
                'security' => true,
            ),
        )));
    }

    public function testOrmSharing()
    {
        $container = $this->createContainer(array(array(
            'role_class' => MockRole::class,
            'permission_class' => MockPermission::class,
            'doctrine' => array(
                'orm' => array(
                    'filters' => array(
                        'sharing' => true,
                    ),
                ),
            ),
        )), array(
            'doctrine.orm.entity_manager.class' => EntityManager::class,
        ));

        $this->assertTrue($container->hasDefinition('sonatra_security.orm.listener.sharing'));
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage The "sonatra_security.doctrine.orm.filter.sharing" config require the "doctrine/orm" package
     */
    public function testOrmSharingWithoutDoctrine()
    {
        $container = $this->createContainer(array(array(
            'role_class' => MockRole::class,
            'permission_class' => MockPermission::class,
            'doctrine' => array(
                'orm' => array(
                    'filters' => array(
                        'sharing' => true,
                    ),
                ),
            ),
        )));

        $this->assertTrue($container->hasDefinition('sonatra_security.orm.listener.sharing'));
    }

    public function testPermission()
    {
        $container = $this->createContainer(array(array(
            'role_class' => MockRole::class,
            'permission_class' => MockPermission::class,
            'permissions' => array(
                MockObject::class => true,
            ),
        )));

        $def = $container->getDefinition('sonatra_security.permission_manager');
        $permConfigs = $def->getArgument(3);

        $this->assertTrue(is_array($permConfigs));
        $this->assertCount(1, $permConfigs);
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage The "FooBar" permission class does not exist
     */
    public function testPermissionWithNonExistentClass()
    {
        $this->createContainer(array(array(
            'role_class' => MockRole::class,
            'permission_class' => MockPermission::class,
            'permissions' => array(
                'FooBar' => true,
            ),
        )));
    }

    public function testPermissionWithFields()
    {
        $container = $this->createContainer(array(array(
            'role_class' => MockRole::class,
            'permission_class' => MockPermission::class,
            'permissions' => array(
                MockObject::class => array(
                    'fields' => array(
                        'id',
                        'name',
                    ),
                ),
            ),
        )));

        $def = $container->getDefinition('sonatra_security.permission_manager');
        $permConfigs = $def->getArgument(3);

        $this->assertTrue(is_array($permConfigs));
        $this->assertCount(1, $permConfigs);
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage The permission field "foo" does not exist in "Sonatra\Component\Security\Tests\Fixtures\Model\MockObject" class
     */
    public function testPermissionWithNonExistentField()
    {
        $this->createContainer(array(array(
            'role_class' => MockRole::class,
            'permission_class' => MockPermission::class,
            'permissions' => array(
                MockObject::class => array(
                    'fields' => array(
                        'foo',
                    ),
                ),
            ),
        )));
    }

    public function testSharing()
    {
        $container = $this->createContainer(array(array(
            'role_class' => MockRole::class,
            'permission_class' => MockPermission::class,
            'sharing_class' => MockSharing::class,
            'sharing' => array(
                'enabled' => true,
                'identity_types' => array(
                    MockRole::class => array(
                        'alias' => 'foo',
                        'roleable' => true,
                        'permissible' => true,
                    ),
                ),
            ),
        )));

        $def = $container->getDefinition('sonatra_security.sharing_manager');
        $identityConfigs = $def->getArgument(0);

        $this->assertTrue(is_array($identityConfigs));
        $this->assertCount(1, $identityConfigs);
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage The "sonatra_security.sharing_class" config must be configured with a valid class
     */
    public function testSharingWithoutSharingClass()
    {
        $container = $this->createContainer(array(array(
            'role_class' => MockRole::class,
            'permission_class' => MockPermission::class,
            'sharing' => array(
                'enabled' => true,
            ),
        )));

        $def = $container->getDefinition('sonatra_security.sharing_manager');
        $identityConfigs = $def->getArgument(0);

        $this->assertTrue(is_array($identityConfigs));
        $this->assertCount(1, $identityConfigs);
    }

    public function testSharingWithDirectAlias()
    {
        $container = $this->createContainer(array(array(
            'role_class' => MockRole::class,
            'permission_class' => MockPermission::class,
            'sharing_class' => MockSharing::class,
            'sharing' => array(
                'enabled' => true,
                'identity_types' => array(
                    MockRole::class => 'foo',
                ),
            ),
        )));

        $def = $container->getDefinition('sonatra_security.sharing_manager');
        $identityConfigs = $def->getArgument(0);

        $this->assertTrue(is_array($identityConfigs));
        $this->assertCount(1, $identityConfigs);
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage The "FooBar" sharing identity class does not exist
     */
    public function testSharingWithNonExistentClass()
    {
        $this->createContainer(array(array(
            'role_class' => MockRole::class,
            'permission_class' => MockPermission::class,
            'sharing_class' => MockSharing::class,
            'sharing' => array(
                'enabled' => true,
                'identity_types' => array(
                    'FooBar' => array(
                        'alias' => 'foo',
                        'roleable' => true,
                        'permissible' => true,
                    ),
                ),
            ),
        )));
    }
}
