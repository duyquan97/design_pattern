<?php

namespace App\Security\Voter;

use App\Entity\Partner;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Class PartnerVoter
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class PartnerVoter extends Voter
{
    const OTA_OPERATION = 'ota';

    /**
     *
     * @param string $attribute
     * @param mixed  $subject
     *
     * @return bool
     */
    protected function supports($attribute, $subject)
    {
        if (static::OTA_OPERATION !== $attribute) {
            return false;
        }

        if (!$subject instanceof Partner) {
            return false;
        }

        return true;
    }

    /**
     *
     * @param string         $attribute
     * @param Partner        $subject
     * @param TokenInterface $token
     *
     * @return bool
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        if (!$subject->getChannelManager()) {
            return false;
        }

        $cmUser = $subject->getChannelManager()->getUser();
        if ($cmUser) {
            return $cmUser === $user;
        }

        return $user === $subject->getUser();
    }
}
