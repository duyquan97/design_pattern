<?php

namespace App\Tests\Controller;

use App\Booking\Model\Booking;
use App\Booking\Model\Experience;
use App\Booking\Model\ExperienceComponent;
use App\Booking\Model\Guest;
use App\Booking\Model\Rate;
use App\Booking\Model\Room;
use App\Entity\Booking as BookingEntity;
use App\Entity\Partner;
use App\Tests\BaseWebTestCase;
use DateTime;
use Zend\Validator\Date;

class BookingManagerTest extends BaseWebTestCase
{

    public function testPushBookings()
    {

        $bookingManager = self::$container->get('App\Booking\BookingManager');
        /** @var Booking $processBooking */
        $processBooking = $this->mockBooking();

        $bookingManager->create($processBooking);

        /** @var BookingEntity $booking */
        $booking = $this->getRepository(BookingEntity::class)->findOneBy(['identifier' => $processBooking->getIdentifier()]);
        $this->assertNotNull($booking);
        $this->assertEquals($booking->getIdentifier(), $processBooking->getIdentifier());
        $this->assertEquals($booking->getPartner()->getIdentifier(), $processBooking->getPartner());
        $this->assertEquals($booking->getBookingProducts()[0]->getProduct()->getIdentifier(), $processBooking->getRoomTypes()[0]->getId());
        $this->assertEquals($booking->getBookingProducts()[0]->getGuests()[0]->getName(), $processBooking->getRoomTypes()[0]->getGuests()[0]->getName());
        $this->assertEquals($booking->getBookingProducts()[0]->getRates()[0]->getAmount(), $processBooking->getRoomTypes()[0]->getDailyRates()[0]->getPrice());
        //$this->assertEquals($booking->getExperience()[0]->getComponents()->getName(), $processBooking->getPartner());

    }

    protected function mockBooking()
    {

        $experienceComponent = new ExperienceComponent();
        $experienceComponent->setName('And people stayed home');

        $experience = new Experience();
        $experience
            ->setPrice('66.6')
            ->setComponents([$experienceComponent]);


        $guest = new Guest();
        $guest
            ->setMain(true)
            ->setAge(41)
            ->setName('Kathleen')
            ->setSurname('O’Meara')
            ->setEmail('kathleen.OMeara@test.tost')
            ->setPhone('312312312')
            ->setCountryCode('VE');

        $rate = new Rate();
        $rate
            ->setDate(new DateTime('2020-01-01'))
            ->setPrice('33.3');
        $room = new Room();
        $room
            ->setId('839233')
            ->setName('Standard Room')
            ->setGuests([$guest])
            ->setDailyRates([$rate]);
        $booking = new Booking();
        $booking
            ->setIdentifier('SBX-123829020')
            ->setStatus('confirm')
            ->setStartDate(new DateTime('2020-01-01'))
            ->setEndDate(new DateTime('2020-01-05'))
            ->setPartner('00029382')
            ->setCreatedAt(new DateTime('2019-11-22T00:00:00'))
            ->setUpdatedAt(new DateTime('2019-11-22T00:00:00'))
            ->setCurrency('EUR')
            ->setVoucherNumber('12312AS23')
            ->setPrice('367.4')
            ->setExperience($experience)
            ->setRoomTypes([$room]);

        return $booking;
    }

}
