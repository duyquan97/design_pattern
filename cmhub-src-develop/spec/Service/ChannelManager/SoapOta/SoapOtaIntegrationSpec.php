<?php

namespace spec\App\Service\ChannelManager\SoapOta;

use App\Exception\AccessDeniedException;
use App\Exception\DateFormatException;
use App\Exception\PartnerNotFoundException;
use App\Exception\SoapOtaOperationNotFoundException;
use App\Service\ChannelManager\SoapOta\SoapOtaIntegration;
use App\Service\ChannelManager\SoapOta\SoapOtaOperationInterface;
use App\Utils\Monolog\CmhubLogger;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class SoapOtaIntegrationSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(SoapOtaIntegration::class);
    }

    function let(CmhubLogger $logger, SoapOtaOperationInterface $operation, SoapOtaOperationInterface $operation1, SoapOtaOperationInterface $operation2)
    {
        $this->beConstructedWith(
            [
                $operation,
                $operation1,
                $operation2
            ],
            $logger,
            'prod'
        );

        $operation->supports('operationA')->willReturn(false);
        $operation1->supports('operationA')->willReturn(false);
        $operation2->supports('operationA')->willReturn(true);

        $operation->supports('operationB')->willReturn(false);
        $operation1->supports('operationB')->willReturn(false);
        $operation2->supports('operationB')->willReturn(false);
    }

    function it_handles_soap_security()
    {
        $this->Security()->shouldBeLike(new \StdClass());
    }

    function it_handles_operation(SoapOtaOperationInterface $operation2, CmhubLogger $logger)
    {
        $logger->addOperationInfo('operationA', null, $this)->shouldBeCalled();
        $operation2->handle($request = (object) ['the' => 'requesst'])->shouldBeCalled()->willReturn($result = ['the_response']);

        $response = (object) $result;
        $response->TimeStamp = (new \DateTime())->format(\DateTime::ISO8601);
        $response->EchoToken = '';
        $response->Version = '1.0';
        $response->Success = new \stdClass();

        $this->operationA($request)->shouldBeLike($response);
    }

    function it_returns_error_response_if_operation_throws_known_exception(CmhubLogger $logger, SoapOtaOperationInterface $operation2, DateFormatException $exception)
    {
        $operation2->handle($request = (object) ['the' => 'requesst', 'EchoToken' => 'fakeToken','Version' => '1.0','TimeStamp'=>'TimeStamp'])->willThrow(new DateFormatException('Y-m-d'));

        $response = json_decode(
            json_encode(
                [
                    'EchoToken' => 'fakeToken',
                    'TimeStamp' => 'TimeStamp',
                    'Version' => '1.0',
                    'Errors' => [
                        'Error' => [
                            [
                                'Code' => 400,
                                '_'    => 'Wrong date format. Expected format is `Y-m-d`'
                            ]
                        ]
                    ]
                ]
            )
        );

        $logger->addOperationInfo('operationA', null, $this)->shouldBeCalled();
        $logger->addOperationException('operationA', Argument::type(DateFormatException::class), $this)->shouldBeCalled();

        $this->operationA($request)->shouldBeLike($response);
    }

    function it_returns_error_response_if_operation_throws_unknown_exception(CmhubLogger $logger, SoapOtaOperationInterface $operation2, DateFormatException $exception)
    {
        $operation2->handle($request = (object) ['the' => 'requesst', 'EchoToken' => 'fakeToken','Version' => '1.0','TimeStamp'=>'TimeStamp'])->willThrow(\ErrorException::class);

        $response = json_decode(
            json_encode(
                [
                    'EchoToken' => 'fakeToken',
                    'TimeStamp' => 'TimeStamp',
                    'Version' => '1.0',
                    'Errors' => [
                        'Error' => [
                            [
                                'Code' => 0,
                                '_'    => 'An error occurred - Please contact administrator'
                            ]
                        ]
                    ]
                ]
            )
        );

        $logger->addOperationInfo('operationA', null, $this)->shouldBeCalled();
        $logger->addOperationException('operationA', Argument::type(\ErrorException::class), $this)->shouldBeCalled();

        $this->operationA($request)->shouldBeLike($response);
    }

    function it_throws_exception_if_integration_not_found(CmhubLogger $logger)
    {
        $logger->addOperationException('operationB', Argument::type(SoapOtaOperationNotFoundException::class), $this)->shouldBeCalled();
        $this
            ->operationB((object)['the' => 'request', 'EchoToken' => 'fakeToken','Version' => '1.0','TimeStamp' => 'TimeStamp'])->shouldBeLike(
            json_decode(json_encode([
                'EchoToken' => 'fakeToken',
                'TimeStamp' => 'TimeStamp',
                'Version' => '1.0',
                'Errors' => [
                    'Error' => [
                        [
                            'Code' => 400,
                            '_'    => SoapOtaOperationNotFoundException::MESSAGE
                        ]
                    ]
                ]
            ])));
    }

    function it_throws_exception_if_missing_partner(
        CmhubLogger $logger,
        SoapOtaOperationInterface $operation2
    )
    {
        $operation2->handle($request = (object) ['the' => 'requesst', 'EchoToken' => 'fakeToken','Version' => '1.0','TimeStamp'=>'TimeStamp'])->willThrow(PartnerNotFoundException::class);
        $response = json_decode(
            json_encode(
                [
                    'EchoToken' => 'fakeToken',
                    'TimeStamp' => 'TimeStamp',
                    'Version' => '1.0',
                    'Errors' => [
                        'Error' => [
                            [
                                'Code' => 400,
                                '_'    => 'The partner with code ``  has not been found'
                            ]
                        ]
                    ]
                ]
            )
        );

        $logger->addOperationInfo('operationA', null, $this)->shouldBeCalled();
        $logger->addOperationException('operationA', Argument::type(PartnerNotFoundException::class), $this)->shouldBeCalled();

        $this->operationA($request)->shouldBeLike($response);
    }

    function it_throws_exception_if_access_denied(
        CmhubLogger $logger,
        SoapOtaOperationInterface $operation2
    )
    {
        $operation2->handle($request = (object) ['the' => 'requesst', 'EchoToken' => 'fakeToken','Version' => '1.0','TimeStamp'=>'TimeStamp'])->willThrow(AccessDeniedException::class);
        $response = json_decode(
            json_encode(
                [
                    'EchoToken' => 'fakeToken',
                    'TimeStamp' => 'TimeStamp',
                    'Version' => '1.0',
                    'Errors' => [
                        'Error' => [
                            [
                                'Code' => 403,
                                '_'    => 'Access Denied'
                            ]
                        ]
                    ]
                ]
            )
        );

        $logger->addOperationInfo('operationA', null, $this)->shouldBeCalled();
        $logger->addOperationException('operationA', Argument::type(AccessDeniedException::class), $this)->shouldBeCalled();

        $this->operationA($request)->shouldBeLike($response);
    }
}
