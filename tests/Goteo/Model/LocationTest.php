<?php


namespace Goteo\Model\Tests;

use Goteo\Model\Location;

class LocationTest extends \PHPUnit_Framework_TestCase {

    public function testInstance() {
        \Goteo\Core\DB::cache(false);

        $location = new Location();

        $this->assertInstanceOf('\Goteo\Model\Location', $location);

        return $location;
    }

    /**
     *
     * @depends testInstance
     */
    public function testDefaultValidation($location) {
        $this->assertFalse($location->validate());
        $this->assertFalse($location->save());
    }

    public function testAddLocationEntry($location) {
        $data = array(
            'location' => 'Simulated City',
            'region' => 'Simulated Region',
            'country' => 'Neverland',
            'lat' => '0.1234567890',
            'lon' => '-0.1234567890',
            'valid' => 1,
            'method' => 'simulated'
        );
        $location = new Location($data);
        $this->assertTrue($location->validate());
        $this->assertTrue($location->save());
        $location2 = Location::get($location->id);
        $this->assertEquals($location->lat, $location2->lat);
        $this->assertEquals($location->lon, $location2->lon);
        $this->assertEquals($location->location, $location2->location);
        $this->assertEquals($location->method, $location2->method);
        $this->assertEquals($location->region, $location2->region);
        $this->assertEquals($location->id, $location2->id);
        //
        return $location2;
    }
    /**
     * @depends  testAddLocationEntry
     */
    public function testRemoveAddLocationEntry($location) {
        $this->assertTrue($location->delete());
        $location2 = Location::get($location->id);
        $this->assertFalse($location2);
    }
}
