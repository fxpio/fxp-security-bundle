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

/**
 * Interface for extensions which provide object filter voters.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
interface ObjectFilterExtensionInterface
{
    /**
     * Replace the value by the filtered value.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public function filterValue($value);
}
