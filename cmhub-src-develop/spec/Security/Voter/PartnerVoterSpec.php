<?php

namespace spec\App\Security\Voter;

use App\Entity\ChannelManager;
use App\Entity\CmUser;
use App\Entity\Partner;
use App\Entity\Product;
use App\Model\PartnerInterface;
use App\Security\Voter\PartnerVoter;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class PartnerVoterSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(PartnerVoter::class);
    }

    function it_returns_false_if_not_supported_attribute(TokenInterface $token, PartnerInterface $partner)
    {
        $this->vote($token, $partner, ['another_attr'])->shouldBe(VoterInterface::ACCESS_ABSTAIN);
    }

    function it_returns_false_if_subject_is_not_a_partner_instance(Product $product, TokenInterface $token)
    {
        $this->vote($token, $product, [PartnerVoter::OTA_OPERATION])->shouldBe(VoterInterface::ACCESS_ABSTAIN);
    }

    function it_grants_access_if_partner_user_same_as_logged_user(TokenInterface $token, ChannelManager $channelManager, CmUser $user, Partner $partner)
    {
        $partner->getChannelManager()->willReturn($channelManager);
        $channelManager->getUser()->willReturn();
        $token->getUser()->willReturn($user);
        $partner->getUser()->willReturn($user);

        $this->vote($token, $partner, [PartnerVoter::OTA_OPERATION])->shouldBe(VoterInterface::ACCESS_GRANTED);
    }

    function it_grants_access_if_channel_manager_user_same_as_logged_user(TokenInterface $token, ChannelManager $channelManager, CmUser $user, Partner $partner)
    {
        $partner->getChannelManager()->willReturn($channelManager);
        $channelManager->getUser()->willReturn($user);
        $token->getUser()->willReturn($user);

        $this->vote($token, $partner, [PartnerVoter::OTA_OPERATION])->shouldBe(VoterInterface::ACCESS_GRANTED);
    }

    function it_does_not_grant_access_if_channel_manager_user_not_same_as_logged_user(TokenInterface $token, ChannelManager $channelManager, CmUser $user, CmUser $user1, Partner $partner)
    {
        $partner->getChannelManager()->willReturn($channelManager);
        $channelManager->getUser()->willReturn($user1);
        $token->getUser()->willReturn($user);

        $this->vote($token, $partner, [PartnerVoter::OTA_OPERATION])->shouldBe(VoterInterface::ACCESS_DENIED);
    }

    function it_does_not_grant_access_if_partner_user_not_same_as_logged_user(TokenInterface $token, ChannelManager $channelManager, CmUser $user, CmUser $user1, Partner $partner)
    {
        $partner->getChannelManager()->willReturn($channelManager);
        $channelManager->getUser()->willReturn();
        $token->getUser()->willReturn($user);
        $partner->getUser()->willReturn($user1);

        $this->vote($token, $partner, [PartnerVoter::OTA_OPERATION])->shouldBe(VoterInterface::ACCESS_DENIED);
    }
}
