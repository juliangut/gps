<?php
/**
 * GPS coordinates manipulation (https://github.com/juliangut/gps)
 *
 * @link https://github.com/juliangut/gps for the canonical source repository
 * @license https://github.com/juliangut/gps/blob/master/LICENSE
 */

namespace Jgut\GPSTests;

use Jgut\Gps\Point;

/**
 * @covers Jgut\Gps\Point
 */
class PointTest extends \PHPUnit_Framework_TestCase
{
    protected $point;

    public function setUp()
    {
        $this->point = new Point();
    }

    /**
     * @covers Jgut\Gps\Point::__construct
     * @covers Jgut\Gps\Point::getLatitude
     * @covers Jgut\Gps\Point::getLongitude
     * @covers Jgut\Gps\Point::get
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid number of arguments
     */
    public function testCreation()
    {
        $this->assertEquals(0, $this->point->getLatitude());
        $this->assertEquals('0°0N', $this->point->getLatitude(Point::FORMAT_DM));
        $this->assertEquals('0°0\'0"N', $this->point->getLatitude(Point::FORMAT_DMS));
        $this->assertEquals(0, $this->point->getLongitude());
        $this->assertEquals('0°0E', $this->point->getLongitude(Point::FORMAT_DM));
        $this->assertEquals('0°0\'0"E', $this->point->getLongitude(Point::FORMAT_DMS));

        $this->assertEquals('0,0', $this->point->get(Point::FORMAT_DD));

        $this->point->set();
    }

    /**
     * @covers Jgut\Gps\Point::__construct
     * @covers Jgut\Gps\Point::set
     * @covers Jgut\Gps\Point::setCoordinate
     * @covers Jgut\Gps\Point::getLatitude
     * @covers Jgut\Gps\Point::getLongitude
     * @covers Jgut\Gps\Point::get
     * @covers Jgut\Gps\Point::getCoordinate
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Argument format is invalid
     */
    public function testSetCreation()
    {
        $point = new Point('22° 57′ 8.7" S, 43° 12\' 42" W'); // Corcovado

        $this->assertEquals(-22.95242, $point->getLatitude());
        $this->assertEquals('22°0.95242S', $point->getLatitude(Point::FORMAT_DM));
        $this->assertEquals('22°57\'8.7"S', $point->getLatitude(Point::FORMAT_DMS));
        $this->assertEquals(-43.21167, $point->getLongitude());
        $this->assertEquals('43°0.21167W', $point->getLongitude(Point::FORMAT_DM));
        $this->assertEquals('43°12\'42"W', $point->getLongitude(Point::FORMAT_DMS));

        $this->assertEquals('22°0.95242S,43°0.21167W', $point->get(Point::FORMAT_DM));

        $this->point->set('0,0,0');
    }

    /**
     * @covers Jgut\Gps\Point::set
     * @covers Jgut\Gps\Point::setCoordinate
     * @covers Jgut\Gps\Point::getLatitude
     * @covers Jgut\Gps\Point::getLongitude
     * @covers Jgut\Gps\Point::get
     * @covers Jgut\Gps\Point::getCoordinate
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Coordinates are not set on a valid format or are not in the same format
     */
    public function testSetDecimalDegrees()
    {
        $this->point->set('41.9, 12.5'); // Rome

        $this->assertEquals(41.9, $this->point->getLatitude());
        $this->assertEquals('41°0.9N', $this->point->getLatitude(Point::FORMAT_DM));
        $this->assertEquals('41°54\'0"N', $this->point->getLatitude(Point::FORMAT_DMS));
        $this->assertEquals(12.5, $this->point->getLongitude());
        $this->assertEquals('12°0.5E', $this->point->getLongitude(Point::FORMAT_DM));
        $this->assertEquals('12°30\'0"E', $this->point->getLongitude(Point::FORMAT_DMS));

        $this->assertEquals('41°54\'0"N,12°30\'0"E', $this->point->get(Point::FORMAT_DMS));

        $this->point->set('41.9,12°0.5E');
    }

    /**
     * @covers Jgut\Gps\Point::set
     * @covers Jgut\Gps\Point::setCoordinate
     * @covers Jgut\Gps\Point::getLatitude
     * @covers Jgut\Gps\Point::getLongitude
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Orientation "S" is not valid for longitude
     */
    public function testFromDecimalMinutes()
    {
        $this->point->set('48° 0.858277778N, 2°0.2945 E'); // Eiffel tower

        $this->assertEquals(48.85828, $this->point->getLatitude());
        $this->assertEquals('48°0.85828N', $this->point->getLatitude(Point::FORMAT_DM));
        $this->assertEquals('48°51\'29.8"N', $this->point->getLatitude(Point::FORMAT_DMS));
        $this->assertEquals(2.2945, $this->point->getLongitude());
        $this->assertEquals('2°0.2945E', $this->point->getLongitude(Point::FORMAT_DM));
        $this->assertEquals('2°17\'40.2"E', $this->point->getLongitude(Point::FORMAT_DMS));

        $this->point->set('48°0.85828N,2°0.2945S');
    }

    /**
     * @covers Jgut\Gps\Point::set
     * @covers Jgut\Gps\Point::setCoordinate
     * @covers Jgut\Gps\Point::setLatitude
     * @covers Jgut\Gps\Point::setLongitude
     * @covers Jgut\Gps\Point::getLatitude
     * @covers Jgut\Gps\Point::getLongitude
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Orientation "E" is not valid for latitude
     */
    public function testFromDegreesMinutesSeconds()
    {
        // Empire State Building
        $this->point->setLatitude('40°44′ 54.3″N');
        $this->point->setLongitude('73° 59′9″ W');

        $this->assertEquals(40.74842, $this->point->getLatitude());
        $this->assertEquals('40°0.74842N', $this->point->getLatitude(Point::FORMAT_DM));
        $this->assertEquals('40°44\'54.3"N', $this->point->getLatitude(Point::FORMAT_DMS));
        $this->assertEquals(-73.98583, $this->point->getLongitude());
        $this->assertEquals('73°0.98583W', $this->point->getLongitude(Point::FORMAT_DM));
        $this->assertEquals('73°59\'9"W', $this->point->getLongitude(Point::FORMAT_DMS));

        $this->point->set('40°44\'54.3"E,73°59\'9"W');
    }

    /**
     * @covers Jgut\Gps\Point::setLatitude
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Coordinate "-100.52" exceeds latitude limits
     */
    public function testLatitudeLimit()
    {
        $this->point->setLatitude(-100.52);
    }

    /**
     * @covers Jgut\Gps\Point::setLatitude
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Coordinate "190.01" exceeds longitude limits
     */
    public function testLongitudeLimit()
    {
        $this->point->setLongitude(190.01);
    }
}
