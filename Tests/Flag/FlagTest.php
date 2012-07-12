<?php

namespace ServerGrove\LocaleBundle\Tests\Flag;

use ServerGrove\LocaleBundle\Flag\Flag;

/**
 * Class FlagTest
 *
 * @author Ismael Ambrosi<ismael@servergrove.com>
 */
class FlagTest extends \PHPUnit_Framework_TestCase
{
    public function testFlag()
    {
        $flag = new Flag('/path/to/flag.png', 'en');

        $this->assertEquals('/path/to/flag.png', $flag->getFile());
        $this->assertEquals($flag->__toString(), $flag->getLocaleString());

        $this->assertNull($flag->getCountry());
        $this->assertEquals($flag->getLocale(), $flag->getLocaleString());

        $flag = new Flag('/path/to/flag.png', 'en', 'GB');

        $this->assertNotNull($flag->getCountry());
        $this->assertEquals($flag->getLocale().'-'.$flag->getCountry(), $flag->getLocaleString());

    }
}
