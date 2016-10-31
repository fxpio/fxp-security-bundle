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

use Symfony\Component\Security\Acl\Voter\FieldVote;

/**
 * The object field view granted event.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class RestoreViewGrantedEvent extends AbstractViewGrantedEvent
{
    /**
     * @var FieldVote
     */
    protected $fieldVote;

    /**
     * @var mixed
     */
    protected $oldValue;

    /**
     * @var mixed
     */
    protected $newValue;

    /**
     * Constructor.
     *
     * @param FieldVote $fieldVote The ACL field vote
     * @param mixed     $oldValue  The old value of field
     * @param mixed     $newValue  The new value of field
     */
    public function __construct(FieldVote $fieldVote, $oldValue, $newValue)
    {
        parent::__construct($fieldVote->getDomainObject());

        $this->fieldVote = $fieldVote;
        $this->oldValue = $oldValue;
        $this->newValue = $newValue;
    }

    /**
     * Get the ACL field vote.
     *
     * @return FieldVote
     */
    public function getFieldVote()
    {
        return $this->fieldVote;
    }

    /**
     * Get the old value of field.
     *
     * @return mixed
     */
    public function getOldValue()
    {
        return $this->oldValue;
    }

    /**
     * Get the new value of field.
     *
     * @return mixed
     */
    public function getNewValue()
    {
        return $this->newValue;
    }
}
