<?php

namespace spec\App\Utils;

use App\Utils\Obfuscator;
use PhpSpec\ObjectBehavior;

class ObfuscatorSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(Obfuscator::class);
    }

    function it_should_obfuscate_passwords()
    {
        $body = "password:\"PasswordToObfuscate\"";
        $this->obfuscate($body)->shouldBe('password:"'.Obfuscator::OBFUSCATED_MESSAGE.'"');

        $body = "MessagePassword:\"PasswordToObfuscate\"";
        $this->obfuscate($body)->shouldBe('MessagePassword:"'.Obfuscator::OBFUSCATED_MESSAGE.'"');
        
        $body = ["password:\"PasswordToObfuscate\""];
        $this->obfuscate($body)->shouldBe(['password:"'.Obfuscator::OBFUSCATED_MESSAGE.'"']);

        $body = ["MessagePassword:\"PasswordToObfuscate\""];
        $this->obfuscate($body)->shouldBe(['MessagePassword:"'.Obfuscator::OBFUSCATED_MESSAGE.'"']);

    }

    function it_should_not_obfuscate_passwords()
    {
        $body = "<wss:Password>PasswordToObfuscate</wss:Password>";
        $this->obfuscate($body)->shouldBe('<wss:Password>PasswordToObfuscate</wss:Password>');

        $body = ["<wss:Password>PasswordToObfuscate</wss:Password>"];
        $this->obfuscate($body)->shouldBe(['<wss:Password>PasswordToObfuscate</wss:Password>']);

    }

}
