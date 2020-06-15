<?php

namespace spec\App\Service\Archives;

use App\Entity\Booking;
use App\Entity\Transaction;
use App\Entity\TransactionChannel;
use App\Entity\TransactionType;
use App\Entity\ChannelManager;
use App\Entity\Partner;
use App\Exception\ValidationException;
use App\Repository\BookingRepository;
use App\Exception\ChannelManagerNotSupportedException;
use App\Exception\CmHubException;
use App\Exception\MissingTransactionDataException;
use App\Model\Factory\PushBookingFactory;
use App\Model\PushBooking;
use App\Service\Archives\DatabaseArchive;
use App\Service\Broadcaster\BookingBroadcaster;
use App\Service\ChannelManager\ChannelManagerInterface;
use App\Service\ChannelManager\ChannelManagerResolver;
use Doctrine\DBAL\Driver\Statement as DriverStatement;
use Doctrine\ORM\EntityManagerInterface;
use PhpSpec\ObjectBehavior;
use PhpSpec\Wrapper\Collaborator;
use \Doctrine\DBAL\Connection;

/**
 * Class DatabaseArchiveSpec
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class DatabaseArchiveSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(DatabaseArchive::class);
    }

    function let(
        EntityManagerInterface $entityManager
    ) {
        $this->beConstructedWith( $entityManager, 'source_table', 'target_table', ['date < :date'], '-3 day');
    }

    function it_create_archive_table(EntityManagerInterface $entityManager, Connection $connection,  DriverStatement $result,DriverStatement $result2,DriverStatement $result3)
    {
        $entityManager->getConnection()->willReturn($connection);
        $connection->prepare('CREATE TABLE IF NOT EXISTS target_table LIKE source_table')->shouldBeCalled()->willReturn($result);
        $connection->prepare("INSERT INTO target_table SELECT * FROM source_table WHERE date < :date")->shouldBeCalled()->willReturn($result2);
        $connection->prepare("DELETE FROM source_table  WHERE id IN (SELECT id FROM target_table WHERE date < :date)")->shouldBeCalled()->willReturn($result3);
        $result->execute(["date" => (date_create('-3 day'))->format('Y-m-d')])->shouldBeCalled()->willReturn(true);
        $result2->execute(["date" => (date_create('-3 day'))->format('Y-m-d')])->shouldBeCalled()->willReturn(true);
        $result3->execute(["date" => (date_create('-3 day'))->format('Y-m-d')])->shouldBeCalled()->willReturn(true);
        $this->archive();
    }

    function it_create_archive_execute_no_result_create(EntityManagerInterface $entityManager, Connection $connection, DriverStatement $result)
    {
        $entityManager->getConnection()->willReturn($connection);
        $connection->prepare('CREATE TABLE IF NOT EXISTS target_table LIKE source_table')->shouldBeCalled()->willReturn($result);
        $result->execute(["date" => (date_create('-3 day'))->format('Y-m-d')])->shouldBeCalledTimes(1)->willReturn(false);
        $this->shouldThrow(\RuntimeException::class)->during('archive');

    }

    function it_create_archive_execute_no_result_insert(EntityManagerInterface $entityManager, Connection $connection, DriverStatement $result,DriverStatement $result2)
    {
        $entityManager->getConnection()->willReturn($connection);
        $connection->prepare('CREATE TABLE IF NOT EXISTS target_table LIKE source_table')->shouldBeCalled()->willReturn($result);
        $result->execute(["date" => (date_create('-3 day'))->format('Y-m-d')])->shouldBeCalledTimes(1)->willReturn(true);
        $connection->prepare("INSERT INTO target_table SELECT * FROM source_table WHERE date < :date")->shouldBeCalled()->willReturn($result2);
        $result2->execute(["date" => (date_create('-3 day'))->format('Y-m-d')])->shouldBeCalledTimes(1)->willReturn(false);
        $this->shouldThrow(\RuntimeException::class)->during('archive');

    }

    function it_create_archive_execute_no_result_delete(EntityManagerInterface $entityManager, Connection $connection, DriverStatement $result,DriverStatement $result2,DriverStatement $result3)
    {
        $entityManager->getConnection()->willReturn($connection);
        $connection->prepare('CREATE TABLE IF NOT EXISTS target_table LIKE source_table')->shouldBeCalled()->willReturn($result);
        $result->execute(["date" => (date_create('-3 day'))->format('Y-m-d')])->shouldBeCalledTimes(1)->willReturn(true);
        $connection->prepare("INSERT INTO target_table SELECT * FROM source_table WHERE date < :date")->shouldBeCalled()->willReturn($result2);
        $result2->execute(["date" => (date_create('-3 day'))->format('Y-m-d')])->shouldBeCalledTimes(1)->willReturn(true);
        $connection->prepare("DELETE FROM source_table  WHERE id IN (SELECT id FROM target_table WHERE date < :date)")->shouldBeCalled()->willReturn($result3);
        $result3->execute(["date" => (date_create('-3 day'))->format('Y-m-d')])->shouldBeCalledTimes(1)->willReturn(false);
        $this->shouldThrow(\RuntimeException::class)->during('archive');

    }

    function it_create_archive_wrong_date(EntityManagerInterface $entityManager)
    {
        $this->beConstructedWith($entityManager, 'source_table', 'target_table', ['date < :date'],'+3 day');
        $this->shouldThrow(ValidationException::class)->during('archive');

    }

    function it_create_archive_get_table_source(EntityManagerInterface $entityManager)
    {
     $this->getTableSource()->shouldBe('source_table');

    }

}
