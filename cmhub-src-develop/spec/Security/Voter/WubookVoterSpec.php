<?php

namespace spec\App\Security\Voter;

use App\Entity\ChannelManager;
use App\Entity\CmUser;
use App\Entity\Partner;
use App\Entity\Product;
use App\Model\PartnerInterface;
use App\Security\Voter\PartnerVoter;
use App\Security\Voter\WubookVoter;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class WubookVoterSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(WubookVoter::class);
    }

    function it_returns_false_if_not_supported_attribute(TokenInterface $token, PartnerInterface $partner)
    {
        $this->vote($token, $partner, ['another_attr'])->shouldBe(VoterInterface::ACCESS_ABSTAIN);
    }

    function it_returns_false_if_subject_is_not_a_partner_instance(Product $product, TokenInterface $token)
    {
        $this->vote($token, $product, [WubookVoter::WUBOOK_OPERATION])->shouldBe(VoterInterface::ACCESS_ABSTAIN);
    }

    function it_does_not_grant_access_if_channel_manager_is_not_wubook(TokenInterface $token, Partner $partner, ChannelManager $channelManager, CmUser $user)
    {
        $token->getUser()->willReturn($user);
        $partner->getChannelManager()->willReturn($channelManager);
        $channelManager->getUser()->willReturn($user);
        $channelManager->getIdentifier()->willReturn("availpro");

        $this->vote($token, $partner, [WubookVoter::WUBOOK_OPERATION])->shouldBe(VoterInterface::ACCESS_DENIED);
    }

    function it_does_grant_access_if_channel_manager_is_wubook(TokenInterface $token, Partner $partner, ChannelManager $channelManager, CmUser $user)
    {
        $token->getUser()->willReturn($user);
        $partner->getChannelManager()->willReturn($channelManager);
        $channelManager->getUser()->willReturn($user);
        $channelManager->getIdentifier()->willReturn("wubook");

        $this->vote($token, $partner, [WubookVoter::WUBOOK_OPERATION])->shouldBe(VoterInterface::ACCESS_GRANTED);
    }
}
