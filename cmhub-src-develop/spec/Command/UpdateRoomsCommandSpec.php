<?php

namespace spec\App\Command;

use App\Command\UpdateRoomsCommand;
use App\Entity\Partner;
use App\Message\Factory\PullRoomFactory;
use App\Message\PullRoom;
use App\Repository\PartnerRepository;
use App\Service\Iresa\IresaBookingEngine;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class UpdateRoomsCommandSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(UpdateRoomsCommand::class);
    }

    function let(MessageBusInterface $messageBus, PullRoomFactory $messageFactory, PartnerRepository $partnerRepository)
    {
        $this->beConstructedWith($messageBus, $messageFactory, $partnerRepository);
    }

    function it_pull_rooms_for_all_partner(InputInterface $input, OutputInterface $output, MessageBusInterface $messageBus, PullRoomFactory $messageFactory,
       PartnerRepository $partnerRepository, OutputFormatterInterface $formatter, Partner $partner1, Partner $partner2, PullRoom $message1, PullRoom $message2)
    {
        $input->getOption(UpdateRoomsCommand::ALL_OPTION)->willReturn(true);
        $input->getOption(UpdateRoomsCommand::PARTNER_OPTION)->willReturn(null);
        $output->getFormatter()->willReturn($formatter);
        $output->isDecorated()->willReturn(false);
        $output->getVerbosity()->willReturn(OutputInterface::VERBOSITY_NORMAL);
        $output->writeln('2 partners to process')->shouldBeCalled();
        $output->writeln('')->shouldBeCalled();
        $output->write(Argument::type('string'))->shouldBeCalled();
        $partnerRepository->iterate([])->willReturn([[$partner1], [$partner2]]);
        $partner1->getIdentifier()->willReturn('id_1');
        $partner2->getIdentifier()->willReturn('id_2');
        $partnerRepository->countByIdentifiers(Argument::any())->willReturn(2);

        $messageFactory->create('id_1')->willReturn($message1);
        $messageFactory->create('id_2')->willReturn($message2);
        $messageBus->dispatch($message1)->shouldBeCalled()->willReturn(new Envelope(new \stdClass()));
        $messageBus->dispatch($message2)->shouldBeCalled()->willReturn(new Envelope(new \stdClass()));

        $this->execute($input, $output);
    }

    function it_pull_rooms_for_some_partner(InputInterface $input, OutputInterface $output, MessageBusInterface $messageBus, PullRoomFactory $messageFactory,
           PartnerRepository $partnerRepository, OutputFormatterInterface $formatter, Partner $partner1, Partner $partner2, PullRoom $message1, PullRoom $message2)
    {
        $input->getOption(UpdateRoomsCommand::ALL_OPTION)->willReturn(null);
        $input->getOption(UpdateRoomsCommand::PARTNER_OPTION)->willReturn('111111,222222');
        $output->getFormatter()->willReturn($formatter);
        $output->isDecorated()->willReturn(false);
        $output->getVerbosity()->willReturn(OutputInterface::VERBOSITY_NORMAL);
        $output->writeln('2 partners to process')->shouldBeCalled();
        $output->writeln('')->shouldBeCalled();
        $output->write(Argument::type('string'))->shouldBeCalled();
        $partnerRepository->iterate(['111111', '222222'])->willReturn([[$partner1], [$partner2]]);
        $partner1->getIdentifier()->willReturn('111111');
        $partner2->getIdentifier()->willReturn('222222');
        $partnerRepository->countByIdentifiers(Argument::any())->willReturn(2);
        $messageFactory->create('111111')->willReturn($message1);
        $messageFactory->create('222222')->willReturn($message2);
        $messageBus->dispatch($message1)->shouldBeCalled()->willReturn(new Envelope(new \stdClass()));
        $messageBus->dispatch($message2)->shouldBeCalled()->willReturn(new Envelope(new \stdClass()));
        $this->execute($input, $output);
    }
}
