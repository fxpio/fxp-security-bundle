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
use Sonatra\Component\Security\Authorization\Voter\ExpressionVoter;
use Sonatra\Component\Security\Authorization\Voter\RoleSecurityIdentityVoter;
use Sonatra\Component\Security\Role\OrganizationalRoleHierarchy;

/**
 * Security extension tests.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class SonatraSecurityExtensionTest extends AbstractSecurityExtensionTest
{
    public function testExtensionExist()
    {
        $container = $this->createContainer();
        $this->assertTrue($container->hasExtension('sonatra_security'));
    }

    public function testObjectFilter()
    {
        $container = $this->createContainer(array(array(
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
            'security_voter' => array(
                'role_security_identity' => true,
                'groupable' => true,
            ),
        )));

        $this->assertTrue($container->hasDefinition('security.access.role_hierarchy_voter'));
        $this->assertTrue($container->hasDefinition('security.access.groupable_voter'));
        $this->assertTrue($container->hasDefinition('sonatra_security.security_identity_retrieval_strategy.listener.group'));

        $this->assertSame('%sonatra_security.access.role_security_identity_voter.class%', $container->getDefinition('security.access.role_hierarchy_voter')->getClass());
        $this->assertSame(RoleSecurityIdentityVoter::class, $container->getParameter('sonatra_security.access.role_security_identity_voter.class'));
    }

    public function testRoleHierarchy()
    {
        $container = $this->createContainer(array(array(
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
            'organizational_context' => array(
                'enabled' => true,
            ),
        )));

        $this->assertTrue($container->hasDefinition('sonatra_security.organizational_context.default'));
        $this->assertTrue($container->hasDefinition('security.access.organization_voter'));
        $this->assertTrue($container->hasDefinition('sonatra_security.security_identity_retrieval_strategy.listener.organization'));
    }

    public function testExpressionLanguage()
    {
        $container = $this->createContainer(array(array(
            'expression' => array(
                'override_voter' => true,
                'functions' => array(
                    'is_basic_auth' => true,
                ),
            ),
        )));

        $this->assertTrue($container->hasDefinition('security.access.expression_voter'));

        $def = $container->getDefinition('security.access.expression_voter');
        $this->assertSame(ExpressionVoter::class, $def->getClass());

        $this->assertTrue($container->hasDefinition('sonatra_security.expression.functions.is_basic_auth'));
    }

    public function testOrmSharing()
    {
        $container = $this->createContainer(array(array(
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
}
