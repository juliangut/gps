<?php
/**
 * GPS coordinates manipulation (https://github.com/juliangut/gps)
 *
 * @link https://github.com/juliangut/gps for the canonical source repository
 * @license https://github.com/juliangut/gps/blob/master/LICENSE
 */

namespace Jgut\Gps;

class Gps
{
    const LATITUDE  = 'latitude';
    const LONGITUDE = 'longitude';

    const FORMAT_DD  = 'decimal_degrees';
    const FORMAT_DM  = 'decimal_minutes';
    const FORMAT_DMS = 'degrees_minutes_seconds';

    const METER     = 'meter';
    const KILOMETER = 'kilometers';

    const EARTH_RADIUS = 6371; // Kilometers
}
