<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\SecurityBundle\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * The abstract commit object filter event.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
abstract class AbstractCommitObjectFilterEvent extends Event
{
    /**
     * @var object[]
     */
    protected $objects;

    /**
     * Constructor.
     *
     * @param object[] $objects The objects
     */
    public function __construct(array $objects)
    {
        $this->objects = $objects;
    }

    /**
     * Get the objects.
     *
     * @return object[]
     */
    public function getObjects()
    {
        return $this->objects;
    }
}
