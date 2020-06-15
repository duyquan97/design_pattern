<?php

namespace spec\App\Service\Loader;

use App\Entity\ChannelManager;
use App\Entity\Partner;
use App\Model\PartnerInterface;
use App\Service\Loader\PartnerLoader;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class PartnerLoaderSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(PartnerLoader::class);
    }

    function let(EntityManagerInterface $entityManager, EntityRepository $repository)
    {
        $this->beConstructedWith($entityManager);

        $entityManager->getRepository(Partner::class)->willReturn($repository);
    }

    function it_finds_partner_by_identifier_and_set_last_accessed_date(EntityManagerInterface $entityManager, EntityRepository $repository, PartnerInterface $partner)
    {
        $repository
            ->findOneBy(
                [
                    'identifier' => 'partner_id',
                    'status'     => 'partner',
                    'enabled'    => true
                ]
            )
            ->shouldBeCalled()
            ->willReturn($partner);

        $this->find('partner_id')->shouldBe($partner);
    }

    function it_returns_null_if_partner_not_found(EntityRepository $repository, PartnerInterface $partner)
    {
        $repository
            ->findOneBy(
                [
                    'identifier' => 'partner_id',
                    'status'     => 'partner',
                    'enabled'    => true
                ]
            )
            ->shouldBeCalled()
            ->willReturn();

        $this->find('partner_id')->shouldBe(null);
    }

    function it_finds_by_ids(EntityManagerInterface $entityManager, EntityRepository $repository, PartnerInterface $partner1, PartnerInterface $partner2)
    {
        $repository
            ->findBy(
                [
                    'identifier' => [
                        'partner_id_1',
                        'partner_id_2'
                    ],
                    'enabled'    => true
                ]
            )
            ->shouldBeCalled()
            ->willReturn([$partner1, $partner2]);

        $this->findByIds(['partner_id_1', 'partner_id_2'])->shouldBe([$partner1, $partner2]);
    }

    function it_finds_by_channel_manager(EntityManagerInterface $entityManager, EntityRepository $repository, PartnerInterface $partner1, PartnerInterface $partner2, ChannelManager $channelManager)
    {
        $repository
            ->findBy(
                [
                    'channelManager' => $channelManager,
                    'enabled'    => true
                ]
            )
            ->shouldBeCalled()
            ->willReturn([$partner1, $partner2]);

        $this->findByChannelManager($channelManager)->shouldBe([$partner1, $partner2]);
    }
}
