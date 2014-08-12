<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\SecurityBundle\Acl\DependencyInjection;

use Sonatra\Bundle\SecurityBundle\Acl\Model\ObjectFilterVoterInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * ACL object filter extension for add the object filter voter.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class ObjectFilterExtension implements ObjectFilterExtensionInterface
{
    /**
     * @var ContainerInterface
     */
    public $container;

    /**
     * @var array
     */
    protected $voterServiceIds;

    /**
     * @var array
     */
    protected $cache;

    /**
     * Constructor.
     *
     * @param array $voterServiceIds
     */
    public function __construct(array $voterServiceIds)
    {
        $this->voterServiceIds = $voterServiceIds;
    }

    /**
     * {@inheritdoc}
     */
    public function filterValue($value)
    {
        foreach ($this->voterServiceIds as $id) {
            $voter = $this->getVoter($id);

            if ($voter->supports($value)) {
                return $voter->getValue($value);
            }
        }

        return null;
    }

    /**
     * Get voter.
     *
     * @param string $id
     *
     * @return ObjectFilterVoterInterface
     */
    protected function getVoter($id)
    {
        if (isset($this->cache[$id])) {
            return $this->cache[$id];
        }

        $this->cache[$id] = $this->container->get($id);

        return $this->cache[$id];
    }
}
