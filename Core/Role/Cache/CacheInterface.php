<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\SecurityBundle\Core\Role\Cache;

/**
 * Role Cache Interface.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
interface CacheInterface
{
    /**
     * Writes cache.
     *
     * @param string $id      The unique name
     * @param string $content The content to write in the cache
     */
    public function write($id, array $roles);

    /**
     * Reads cache.
     *
     * @param string $id The unique name
     *
     * @return array|null The roles
     */
    public function read($id);

    /**
     * Invalidates cache.
     */
    public function flush();
}
