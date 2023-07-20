<?php

namespace Tests\Unit;

use App\Libs\TicketNumberGenerator;
use PHPUnit\Framework\TestCase;

class TicketNumberGeneratorTest extends TestCase
{
    public function testGenerate()
    {
        $company_id = 123;
        $countTicket = 10;
        $expectedTicketNumber = 'TICKET123010';

        $ticketNumber = TicketNumberGenerator::generate($company_id, $countTicket);

        $this->assertEquals($expectedTicketNumber, $ticketNumber);
    }
}
