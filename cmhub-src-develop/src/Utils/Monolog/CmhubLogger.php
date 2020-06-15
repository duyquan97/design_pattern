<?php

namespace App\Utils\Monolog;

use App\Entity\CmUser;
use App\Entity\Transaction;
use App\Exception\AccessDeniedException;
use App\Model\PartnerInterface;
use App\Model\PushBooking;
use App\Utils\Obfuscator;
use Monolog\Logger as MonologLogger;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class CmhubLogger
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class CmhubLogger
{

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var Obfuscator
     */
    private $obfuscator;

    /**
     * Logger constructor.
     *
     * @param LoggerInterface       $logger
     * @param TokenStorageInterface $tokenStorage
     * @param RequestStack          $requestStack
     * @param Obfuscator            $obfuscator
     */
    public function __construct(LoggerInterface $logger, TokenStorageInterface $tokenStorage, RequestStack $requestStack, Obfuscator $obfuscator)
    {
        $this->logger = $logger;
        $this->tokenStorage = $tokenStorage;
        $this->requestStack = $requestStack;
        $this->obfuscator = $obfuscator;
    }

    /**
     * @param string     $operation
     * @param \Throwable $exception
     * @param mixed      $module
     *
     * @return void
     */
    public function addOperationException(string $operation, \Throwable $exception, $module = ''): void
    {
        $data = $this->getUserAndRequestInfo();
        $dataLog = [
            LogKey::TYPE_KEY      => LogType::getExceptionType($exception),
            LogKey::EX_TYPE_KEY   => LogType::getExtendedExceptionType($exception),
            LogKey::USERNAME_KEY  => $data[LogKey::USERNAME_KEY],
            LogKey::CM_KEY        => $data[LogKey::CM_KEY],
            LogKey::OPERATION_KEY => $operation,
            LogKey::REQUEST_KEY   => $data[LogKey::REQUEST_KEY],
            LogKey::LINE_KEY      => $exception->getLine(),
            LogKey::TRACE_KEY     => $exception->getTraceAsString(),
        ];
        if ($exception instanceof AccessDeniedException) {
            $dataLog[LogKey::PARTNER_ID_KEY] = $exception->getHotelCode();
        }

        $this->addRecord(
            MonologLogger::ERROR,
            $exception->getMessage(),
            $dataLog,
            $module
        );
    }

    /**
     *
     * @param PushBooking $pushBooking
     * @param string      $request
     * @param string      $response
     *
     * @return void
     */
    public function addPushBookingSent(PushBooking $pushBooking, $request, $response)
    {
        $this
            ->addRecord(
                MonologLogger::INFO,
                'Booking sent to CM',
                [
                    LogKey::TYPE_KEY       => LogType::PUSH_BOOKING,
                    LogKey::STATUS_KEY     => 'success',
                    LogKey::ACTION_KEY     => LogAction::PUSH_BOOKING_SENT,
                    LogKey::REQUEST_KEY    => $request,
                    LogKey::RESPONSE_KEY   => $response,
                    LogKey::PARTNER_ID_KEY => $pushBooking->getBooking()->getPartner()->getIdentifier(),
                    LogKey::IDENTIFIER_KEY => $pushBooking->getBooking()->getIdentifier(),
                    LogKey::CM_KEY => ($pushBooking->getBooking()->getPartner()->getChannelManager()) ? $pushBooking->getBooking()->getPartner()->getChannelManager()->getIdentifier() : '',
                ]
            );
    }

    /**
     *
     * @param string      $message
     * @param PushBooking $pushBooking
     * @param mixed       $module
     * @param string      $request
     *
     * @return void
     */
    public function addPushBookingException($message, PushBooking $pushBooking, $module, $request = null): void
    {
        $booking = $pushBooking->getBooking();
        $data = $this->getUserAndRequestInfo();

        $this->addRecord(
            MonologLogger::ERROR,
            'Push booking failed',
            [
                LogKey::TYPE_KEY           => LogType::PUSH_BOOKING,
                LogKey::ACTION_KEY         => LogAction::PUSH_BOOKING_FAILED,
                LogKey::STATUS_KEY         => 'failed',
                LogKey::EX_TYPE_KEY        => 'unknown',
                LogKey::USERNAME_KEY       => $data[LogKey::USERNAME_KEY],
                LogKey::CM_KEY             => $data[LogKey::CM_KEY],
                LogKey::BOOKING_STATUS_KEY => $booking->getStatus(),
                LogKey::REQUEST_KEY        => null === $request ? $data[LogKey::REQUEST_KEY] : $request,
                LogKey::PARTNER_ID_KEY     => ($partner = $booking->getPartner()) ? $partner->getIdentifier() : '',
                LogKey::IDENTIFIER_KEY     => $booking->getIdentifier(),
                LogKey::RESPONSE_KEY       => $message,
            ],
            $module
        );
    }

    /**
     * @param string     $operation
     * @param \Throwable $exception
     * @param string     $module
     *
     * @return void
     */
    public function addException(string $operation, \Throwable $exception, $module = ''): void
    {
        $data = $this->getUserAndRequestInfo();

        $this->addRecord(
            MonologLogger::ERROR,
            $exception->getMessage(),
            [
                LogKey::TYPE_KEY      => LogType::getExceptionType($exception),
                LogKey::EX_TYPE_KEY   => LogType::getExtendedExceptionType($exception),
                LogKey::USERNAME_KEY  => $data[LogKey::USERNAME_KEY],
                LogKey::CM_KEY        => $data[LogKey::CM_KEY],
                LogKey::OPERATION_KEY => $operation,
            ],
            $module
        );
    }

    /**
     *
     * @param string                $operation
     * @param PartnerInterface|null $partner
     * @param string                $module
     *
     * @return void
     */
    public function addOperationInfo(string $operation, PartnerInterface $partner = null, $module = ''): void
    {
        $data = $this->getUserAndRequestInfo();
        $log = [
            LogKey::TYPE_KEY      => LogAction::CM_OPERATION,
            LogKey::ACTION_KEY    => $operation,
            LogKey::OPERATION_KEY => $operation,
            LogKey::REQUEST_KEY   => $data[LogKey::REQUEST_KEY],
            LogKey::USERNAME_KEY  => $data[LogKey::USERNAME_KEY],
        ];

        if ($partner) {
            $log = array_merge(
                $log,
                [
                    LogKey::PARTNER_ID_KEY   => $partner->getIdentifier(),
                    LogKey::PARTNER_NAME_KEY => $partner->getName(),
                    LogKey::CM_KEY           => ($partner->getChannelManager()) ? $partner->getChannelManager()->getIdentifier() : '',
                ]
            );
        }

        $this
            ->addRecord(
                MonologLogger::INFO,
                $operation,
                $log,
                $module
            );
    }

    /**
     * @param Transaction $transaction
     * @param mixed       $module
     * @param string      $message
     *
     * @return void
     */
    public function addTransactionInfo(Transaction $transaction, $module = '', $message = 'Transaction processed'): void
    {
        $data = $this->getUserAndRequestInfo();
        $logData = $transaction->toArray();
        $logData[LogKey::REQUEST_KEY] = $data[LogKey::REQUEST_KEY];
        $logData[LogKey::USERNAME_KEY] = $data[LogKey::USERNAME_KEY];

        $this
            ->addRecord(
                MonologLogger::INFO,
                $message,
                $logData,
                $module
            );
    }

    /**
     * @param int    $level
     * @param string $message
     * @param array  $context
     * @param mixed  $module
     *
     * @return void
     */
    public function addRecord(int $level, string $message, array $context = [], $module = ''): void
    {
        $context['mypid'] = getmypid() ?: null;
        $context['session_id'] = session_id() ?: null;
        $module = \is_object($module) ? \get_class($module) : (string) $module;
        $context['module'] = $module;

        $context = $this->obfuscator->obfuscate($context);

        foreach ($context as $key => $value) {
            if (empty($value) && !is_bool($value) && !is_numeric($value)) {
                unset($context[$key]);
            }
        }

        $this->logger->log($level, $this->obfuscator->obfuscate($message), $context);
    }

    /**
     * @return array
     */
    private function getUserAndRequestInfo(): array
    {
        $token = $this->tokenStorage->getToken();
        $username = $cmIdentifier = '';
        if (($token && $user = $token->getUser()) && $user instanceof UserInterface) {
            $username = $user->getUsername();
            if ($user instanceof CmUser) {
                $cmIdentifier = ($channelManager = $user->getChannelManager()) ? $channelManager->getIdentifier() : '';
            }
        }

        $request = ($currentRequest = $this->requestStack->getCurrentRequest()) ? $currentRequest->getContent() : '';

        return [
            LogKey::USERNAME_KEY => $username,
            LogKey::CM_KEY       => $cmIdentifier,
            LogKey::REQUEST_KEY  => $request,
        ];
    }
}
