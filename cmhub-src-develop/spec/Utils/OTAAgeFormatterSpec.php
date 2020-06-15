<?php

namespace spec\App\Utils;

use App\Utils\OTAAgeFormatter;
use PhpSpec\ObjectBehavior;

class OTAAgeFormatterSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(OTAAgeFormatter::class);
    }

    function it_should_return_seven_if_age_less_than_three()
    {
        $this::format(0)->shouldReturn(1);
        $this::format(2)->shouldReturn(7);
        $this::format(1)->shouldReturn(7);
        $this::format(3)->shouldNotReturn(7);
    }

    function it_should_return_eight_if_age_less_than_twelve()
    {
        $this::format(10)->shouldReturn(8);
        $this::format(11)->shouldReturn(8);
        $this::format(12)->shouldNotReturn(8);
    }

    function it_should_return_ten_if_age_less_than_sixty_five()
    {
        $this::format(64)->shouldReturn(10);
        $this::format(50)->shouldReturn(10);
        $this::format(65)->shouldNotReturn(10);
    }

    function it_should_return_eleven_if_age_greater_or_equals_than_sixty_five()
    {
        $this::format(66)->shouldReturn(11);
        $this::format(65)->shouldReturn(11);
        $this::format(60)->shouldNotReturn(11);
    }
}
