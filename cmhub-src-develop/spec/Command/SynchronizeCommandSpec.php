<?php

namespace spec\App\Command;

use App\Command\SynchronizeCommand;
use App\Message\Factory\SyncDataFactory;
use App\Message\SyncData;
use App\Model\PartnerInterface;
use App\Repository\PartnerRepository;
use App\Service\Synchronizer\AvailabilityForcedAlignment;
use App\Service\Synchronizer\PriceForcedAlignment;
use Doctrine\ORM\Internal\Hydration\IterableResult;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class SynchronizeCommandSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(SynchronizeCommand::class);
    }

    function let(PartnerRepository $partnerRepository, MessageBusInterface $messageBus, SyncDataFactory $messageFactory)
    {
        $this->beConstructedWith($partnerRepository, $messageBus, $messageFactory);
    }

    function it_sync_with_date_period(
        InputInterface $input,
        OutputInterface $output,
        PartnerRepository $partnerRepository,
        PartnerInterface $partner,
        SyncDataFactory $messageFactory,
        SyncData $message,
        SyncData $message1,
        MessageBusInterface $messageBus
    )
    {
        $start = new \DateTime();
        $end = new \DateTime('+1 month');
        $input->getOption(SynchronizeCommand::BATCH_OPTION)->willReturn(null);
        $input->getOption(SynchronizeCommand::PERIOD_OPTION)->willReturn('+1 month');
        $input->getOption(SynchronizeCommand::START_OPTION)->willReturn(null);
        $input->getOption(SynchronizeCommand::END_OPTION)->willReturn(null);
        $input->getOption(SynchronizeCommand::LIMIT_OPTION)->willReturn(null);
        $input->getOption(SynchronizeCommand::OFFSET_OPTION)->willReturn(null);
        $input->getOption(SynchronizeCommand::AVAILABILITY_OPTION)->willReturn(true);
        $input->getOption(SynchronizeCommand::PRICE_OPTION)->willReturn(true);
        $input->getOption(SynchronizeCommand::PARTNER_OPTION)->willReturn('00019091');
        $output->isDecorated()->willReturn(true);
        $output->getVerbosity()->willReturn(OutputInterface::VERBOSITY_QUIET);

        $partnerRepository->iterate(['00019091'],null,null)->willReturn([[$partner]]);
        $partnerRepository->countByIdentifiers(Argument::any())->willReturn(1);
        $output->writeln('1 partners to process')->shouldBeCalled();
        $output->writeln('')->shouldBeCalled();
        $output->writeln(sprintf('Align availabilities from %s to %s', $start->format('Y-m-d'), $end->format('Y-m-d')))->shouldBeCalled();
        $output->writeln(sprintf('Align prices from %s to %s', $start->format('Y-m-d'), $end->format('Y-m-d')))->shouldBeCalled();
        $partner->getIdentifier()->willReturn('00019091');

        $messageFactory->create('00019091', AvailabilityForcedAlignment::TYPE,
            Argument::that(function($start) {
                return $start instanceof \DateTime;
            }),
            Argument::that(function($end) {
                return $end instanceof \DateTime;
            }))->willReturn($message);
        $messageFactory->create('00019091', PriceForcedAlignment::TYPE,
            Argument::that(function($start) {
                return $start instanceof \DateTime;
            }),
            Argument::that(function($end) {
                return $end instanceof \DateTime;
            }))->willReturn($message1);
        $messageBus->dispatch($message)->shouldBeCalled()->willReturn(new Envelope($message));
        $messageBus->dispatch($message1)->shouldBeCalled()->willReturn(new Envelope($message1));

        $this->execute($input, $output);
    }

    function it_sync_with_start_date_end_date(
        InputInterface $input,
        OutputInterface $output,
        PartnerRepository $partnerRepository,
        PartnerInterface $partner,
        SyncDataFactory $messageFactory,
        SyncData $message,
        SyncData $message1,
        MessageBusInterface $messageBus
    )
    {
        $start = new \DateTime('2019-02-12');
        $end = new \DateTime('2019-03-01');
        $input->getOption(SynchronizeCommand::BATCH_OPTION)->willReturn(null);
        $input->getOption(SynchronizeCommand::PERIOD_OPTION)->willReturn(null);
        $input->getOption(SynchronizeCommand::LIMIT_OPTION)->willReturn(null);
        $input->getOption(SynchronizeCommand::OFFSET_OPTION)->willReturn(null);
        $input->getOption(SynchronizeCommand::START_OPTION)->willReturn('2019-02-12');
        $input->getOption(SynchronizeCommand::END_OPTION)->willReturn('2019-03-01');
        $input->getOption(SynchronizeCommand::AVAILABILITY_OPTION)->willReturn(true);
        $input->getOption(SynchronizeCommand::PRICE_OPTION)->willReturn(true);
        $input->getOption(SynchronizeCommand::PARTNER_OPTION)->willReturn('00019091');
        $output->isDecorated()->willReturn(true);
        $output->getVerbosity()->willReturn(OutputInterface::VERBOSITY_QUIET);

        $partnerRepository->iterate(['00019091'], null, null)->shouldBeCalled()->willReturn([[$partner]]);
        $partnerRepository->countByIdentifiers(Argument::any())->willReturn(1);

        $output->writeln('1 partners to process')->shouldBeCalled();
        $output->writeln('')->shouldBeCalled();
        $output->writeln(sprintf('Align availabilities from %s to %s', $start->format('Y-m-d'), $end->format('Y-m-d')))->shouldBeCalled();
        $output->writeln(sprintf('Align prices from %s to %s', $start->format('Y-m-d'), $end->format('Y-m-d')))->shouldBeCalled();
        $partner->getIdentifier()->willReturn('00019091');
        $messageFactory->create('00019091', AvailabilityForcedAlignment::TYPE,
            Argument::that(function($start) {
                return $start instanceof \DateTime;
            }),
            Argument::that(function($end) {
                return $end instanceof \DateTime;
            }))->willReturn($message);
        $messageFactory->create('00019091', PriceForcedAlignment::TYPE,
            Argument::that(function($start) {
                return $start instanceof \DateTime;
            }),
            Argument::that(function($end) {
                return $end instanceof \DateTime;
            }))->willReturn($message1);
        $messageBus->dispatch($message)->shouldBeCalled()->willReturn(new Envelope($message));
        $messageBus->dispatch($message1)->shouldBeCalled()->willReturn(new Envelope($message1));

        $this->execute($input, $output);
    }

    function it_get_invalid_period(InputInterface $input, OutputInterface $output, PartnerRepository $partnerRepository)
    {
        $input->getOption(SynchronizeCommand::PERIOD_OPTION)->willReturn('some year');
        $input->getOption(SynchronizeCommand::BATCH_OPTION)->willReturn(null);
        $input->getOption(SynchronizeCommand::END_OPTION)->shouldNotBeCalled();
        $input->getOption(SynchronizeCommand::START_OPTION)->shouldNotBeCalled();
        $input->getOption(SynchronizeCommand::PARTNER_OPTION)->shouldNotBeCalled();

        $partnerRepository->iterate(Argument::any())->shouldNotBeCalled();

        $this->execute($input, $output);
    }

    function it_get_start_date_greater_than_end_date(InputInterface $input, OutputInterface $output, PartnerRepository $partnerRepository)
    {
        $input->getOption(SynchronizeCommand::BATCH_OPTION)->willReturn(null);
        $input->getOption(SynchronizeCommand::PERIOD_OPTION)->willReturn(null);
        $input->getOption(SynchronizeCommand::START_OPTION)->willReturn('2019-04-01');
        $input->getOption(SynchronizeCommand::END_OPTION)->willReturn('2019-03-01');
        $input->getOption(SynchronizeCommand::AVAILABILITY_OPTION)->willReturn(true);
        $input->getOption(SynchronizeCommand::PRICE_OPTION)->willReturn(true);
        $input->getOption(SynchronizeCommand::PARTNER_OPTION)->shouldNotBeCalled();

        $output->writeln('Start date cant be greater than end date')->shouldBeCalled();
        $partnerRepository->iterate(Argument::any())->shouldNotBeCalled();

        $this->execute($input, $output);
    }

    function it_get_invalid_start_date(InputInterface $input, OutputInterface $output, PartnerRepository $partnerRepository)
    {
        $input->getOption(SynchronizeCommand::BATCH_OPTION)->willReturn(null);
        $input->getOption(SynchronizeCommand::PERIOD_OPTION)->willReturn(null);
        $input->getOption(SynchronizeCommand::START_OPTION)->willReturn('01-1997-01');
        $input->getOption(SynchronizeCommand::AVAILABILITY_OPTION)->willReturn(true);
        $input->getOption(SynchronizeCommand::PRICE_OPTION)->willReturn(true);
        $input->getOption(SynchronizeCommand::PARTNER_OPTION)->shouldNotBeCalled();

        $output->writeln('Wrong date format. Expected format is `Y-m-d`')->shouldBeCalled();
        $partnerRepository->iterate(Argument::any())->shouldNotBeCalled();

        $this->execute($input, $output);
    }

    function it_get_invalid_end_date(InputInterface $input, OutputInterface $output, PartnerRepository $partnerRepository)
    {
        $input->getOption(SynchronizeCommand::BATCH_OPTION)->willReturn(null);
        $input->getOption(SynchronizeCommand::PERIOD_OPTION)->willReturn(null);
        $input->getOption(SynchronizeCommand::START_OPTION)->willReturn('1997-01-01');
        $input->getOption(SynchronizeCommand::END_OPTION)->willReturn('1-1997-01-01');
        $input->getOption(SynchronizeCommand::AVAILABILITY_OPTION)->willReturn(true);
        $input->getOption(SynchronizeCommand::PRICE_OPTION)->willReturn(true);
        $input->getOption(SynchronizeCommand::PARTNER_OPTION)->shouldNotBeCalled();

        $output->writeln('Wrong date format. Expected format is `Y-m-d`')->shouldBeCalled();
        $partnerRepository->iterate(Argument::any())->shouldNotBeCalled();

        $this->execute($input, $output);
    }

    function it_does_not_provide_start_date_end_date(
        InputInterface $input,
        OutputInterface $output,
        PartnerRepository $partnerRepository,
        PartnerInterface $partner,
        IterableResult $result,
        SyncDataFactory $messageFactory,
        SyncData $message,
        SyncData $message1,
        MessageBusInterface $messageBus
    )
    {
        $start = new \DateTime();
        $end = new \DateTime('+1 year');
        $input->getOption(SynchronizeCommand::BATCH_OPTION)->willReturn(null);
        $input->getOption(SynchronizeCommand::PERIOD_OPTION)->willReturn(null);
        $input->getOption(SynchronizeCommand::START_OPTION)->willReturn(null);
        $input->getOption(SynchronizeCommand::LIMIT_OPTION)->willReturn(null);
        $input->getOption(SynchronizeCommand::OFFSET_OPTION)->willReturn(null);
        $input->getOption(SynchronizeCommand::END_OPTION)->willReturn(null);
        $input->getOption(SynchronizeCommand::AVAILABILITY_OPTION)->willReturn(true);
        $input->getOption(SynchronizeCommand::PRICE_OPTION)->willReturn(true);
        $input->getOption(SynchronizeCommand::PARTNER_OPTION)->willReturn('00019091');
        $output->isDecorated()->willReturn(true);
        $output->getVerbosity()->willReturn(OutputInterface::VERBOSITY_QUIET);

        $partnerRepository->iterate(['00019091'], null, null)->willReturn([[$partner]]);
        $partnerRepository->countByIdentifiers(['00019091'])->willReturn(1);

        $output->writeln('1 partners to process')->shouldBeCalled();
        $output->writeln('')->shouldBeCalled();

        $output->writeln(sprintf('Align availabilities from %s to %s', $start->format('Y-m-d'), $end->format('Y-m-d')))->shouldBeCalled();
        $output->writeln(sprintf('Align prices from %s to %s', $start->format('Y-m-d'), $end->format('Y-m-d')))->shouldBeCalled();
        $partner->getIdentifier()->willReturn('00019091');
        $messageFactory->create('00019091', AvailabilityForcedAlignment::TYPE,
            Argument::that(function($start) {
                return $start instanceof \DateTime;
            }),
            Argument::that(function($end) {
                return $end instanceof \DateTime;
            }))->willReturn($message);
        $messageFactory->create('00019091', PriceForcedAlignment::TYPE,
            Argument::that(function($start) {
                return $start instanceof \DateTime;
            }),
            Argument::that(function($end) {
                return $end instanceof \DateTime;
            }))->willReturn($message1);
        $messageBus->dispatch($message)->shouldBeCalled()->willReturn(new Envelope($message));
        $messageBus->dispatch($message1)->shouldBeCalled()->willReturn(new Envelope($message1));

        $this->execute($input, $output);
    }

    function it_synchronize_with_exception(
        InputInterface $input,
        OutputInterface $output,
        PartnerRepository $partnerRepository,
        PartnerInterface $partner
    )
    {
        $start = new \DateTime();
        $end = new \DateTime('+1 month');
        $input->getOption(SynchronizeCommand::BATCH_OPTION)->willReturn(null);
        $input->getOption(SynchronizeCommand::PERIOD_OPTION)->willReturn('+1 month');
        $input->getOption(SynchronizeCommand::START_OPTION)->willReturn(null);
        $input->getOption(SynchronizeCommand::LIMIT_OPTION)->willReturn(null);
        $input->getOption(SynchronizeCommand::OFFSET_OPTION)->willReturn(null);
        $input->getOption(SynchronizeCommand::END_OPTION)->willReturn(null);
        $input->getOption(SynchronizeCommand::AVAILABILITY_OPTION)->willReturn(true);
        $input->getOption(SynchronizeCommand::PRICE_OPTION)->willReturn(true);
        $input->getOption(SynchronizeCommand::PARTNER_OPTION)->willReturn('00019091');
        $output->isDecorated()->willReturn(true);
        $output->getVerbosity()->willReturn(OutputInterface::VERBOSITY_QUIET);

        $partnerRepository->iterate(['00019091'], null, null)->willReturn([$partner]);
        $partnerRepository->countByIdentifiers(['00019091'])->willReturn(1);

        $output->writeln('1 partners to process')->shouldBeCalled();
        $output->writeln('')->shouldBeCalled();
        $output->writeln(sprintf('Align availabilities from %s to %s', $start->format('Y-m-d'), $end->format('Y-m-d')))->shouldBeCalled();
        $output->writeln(sprintf('Align prices from %s to %s', $start->format('Y-m-d'), $end->format('Y-m-d')))->shouldBeCalled();

        $this->shouldThrow(\Throwable::class)->during('execute', [$input, $output]);
    }
}

