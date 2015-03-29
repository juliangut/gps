<?php
/**
 * GPS coordinates manipulation (https://github.com/juliangut/gps)
 *
 * @link https://github.com/juliangut/gps for the canonical source repository
 * @license https://github.com/juliangut/gps/blob/master/LICENSE
 */

namespace Jgut\Gps;

class Point
{
    const LATITUDE  = 'latitude';
    const LONGITUDE = 'longitude';

    const FORMAT_DD  = 'decimal_degrees';
    const FORMAT_DM  = 'decimal_minutes';
    const FORMAT_DMS = 'degrees_minutes_seconds';

    /**
     * Format for decimal degrees coordinates
     *
     * @var string
     */
    protected $decimalDegreesFormat = '-?\d+(\.\d+)?';

    /**
     * Format for decimal minutes coordinates
     *
     * @var string
     */
    protected $decimalMinutesFormat = '(\d+)°(\d+(\.\d+)?)([NSEW])';

    /**
     * Format for degrees minutes seconds coordinates
     *
     * @var string
     */
    protected $degreesMinutesSecondsFormat = '(\d+)°(\d+)\'(\d+(\.\d+)?)"([NSEW])';

    /**
     * @var float
     */
    protected $latitude = 0.0;

    /**
     * @var float
     */
    protected $longitude = 0.0;

    public function __construct()
    {
        $args = func_get_args();

        if (count($args) > 0) {
            call_user_func_array(array($this, 'set'), $args);
        }
    }

    /**
     * Set GPS coordinates
     *
     * Expects one or two string arguments corresponding to both GPS coordinates
     * separated by a comma or separate coordinates
     *
     * @return $this
     */
    public function set()
    {
        $coordinates = $this->normalizeParameterCoordinates(array_slice(func_get_args(), 0, 2));

        if ((preg_match('!^' . $this->decimalDegreesFormat . '$!', $coordinates[0])
            && !preg_match('!^' . $this->decimalDegreesFormat . '$!', $coordinates[1]))
            || (preg_match('!^' . $this->decimalMinutesFormat . '$!', $coordinates[0])
            && !preg_match('!^' . $this->decimalMinutesFormat . '$!', $coordinates[0]))
            || (preg_match('!^' . $this->degreesMinutesSecondsFormat . '$!', $coordinates[0])
            && !preg_match('!^' . $this->degreesMinutesSecondsFormat . '$!', $coordinates[0]))
        ) {
            throw new \InvalidArgumentException(sprintf(
                'Coordinates are not set on a valid format or are not in the same format',
                $coordinates
            ));
        }

        $this->setCoordinate($coordinates[0], Gps::LATITUDE);
        $this->setCoordinate($coordinates[1], Gps::LONGITUDE);

        return $this;
    }

    /**
     * Normalize coordinates given as separate parameters
     *
     * @param array $coordinates
     * @return array
     */
    protected function normalizeParameterCoordinates(array $coordinates)
    {
        if (count($coordinates) == 0 || count($coordinates) > 2) {
            throw new \InvalidArgumentException('Invalid number of arguments');
        }

        if (count($coordinates) == 1) {
            $coordinates = explode(',', $coordinates[0]);

            if (count($coordinates) !== 2) {
                throw new \InvalidArgumentException('Argument format is invalid');
            }
        }

        return array_map(array($this, 'normalizeCoordinate'), $coordinates);
    }

    /**
     * Set latitude coordinate
     *
     * @param mixed $latitude
     * @return $this
     */
    public function setLatitude($latitude)
    {
        $this->setCoordinate($this->normalizeCoordinate($latitude), Gps::LATITUDE);

        return $this;
    }

    /**
     * Set longitude coordinate
     *
     * @param mixed $longitude
     * @return $this
     */
    public function setLongitude($longitude)
    {
        $this->setCoordinate($this->normalizeCoordinate($longitude), Gps::LONGITUDE);

        return $this;
    }

    /**
     * Normalize coordinate format
     *
     * @param string $coordinate
     * @return string
     */
    final protected function normalizeCoordinate($coordinate)
    {
        return str_replace(array(' ', '′', '″'), array('', "'", '"'), $coordinate);
    }

    /**
     * Set coordinate
     *
     * @param mixed $value
     * @param string $coordinate
     */
    protected function setCoordinate($value, $coordinate = Gps::LATITUDE)
    {
        if (preg_match('!^' . $this->decimalDegreesFormat . '$!', $value, $matches)) {
            $result = floatval($matches[0]);
        } elseif (preg_match('!^' . $this->decimalMinutesFormat . '$!', $value, $matches)) {
            $this->validateCoordinateOrientation($matches[4], $coordinate);

            $result = intval($matches[1]) + floatval($matches[2]);
            $result *= in_array($matches[4], array('S', 'W')) ? -1 : 1;
        } elseif (preg_match('!^' . $this->degreesMinutesSecondsFormat . '$!', $value, $matches)) {
            $this->validateCoordinateOrientation($matches[5], $coordinate);

            $result = intval($matches[1]) + (intval($matches[2]) / 60) + (floatval($matches[3]) / 3600);
            $result *= in_array($matches[5], array('S', 'W')) ? -1 : 1;
        } else {
            throw new \InvalidArgumentException(sprintf('Coordinate "%s" is not set on a valid format', $value));
        }

        if ($coordinate === Gps::LATITUDE) {
            if (abs($result) > 90) {
                throw new \InvalidArgumentException(sprintf('Coordinate "%s" exceeds latitude limits', $value));
            }

            $this->latitude = $result;
        } else {
            if (abs($result) > 180) {
                throw new \InvalidArgumentException(sprintf('Coordinate "%s" exceeds longitude limits', $value));
            }
            $this->longitude = $result;
        }
    }

    /**
     * Checks validity of orientation char
     *
     * @param string $orientation
     * @param string $coordinate
     */
    final protected function validateCoordinateOrientation($orientation, $coordinate = Gps::LATITUDE)
    {
        if ($coordinate === Gps::LATITUDE && !in_array($orientation, array('N', 'S'))
            || $coordinate === Gps::LONGITUDE && !in_array($orientation, array('E', 'W'))
        ) {
            throw new \InvalidArgumentException(sprintf(
                'Orientation "%s" is not valid for %s',
                $orientation,
                $coordinate
            ));
        }
    }

    /**
     * Retrieve formatted GPS point
     *
     * @param string $format
     * @return string
     */
    public function get($format = Gps::FORMAT_DD)
    {
        return sprintf('%s,%s', $this->getLatitude($format), $this->getLongitude($format));
    }

    /**
     * Retrieve formatted latitude
     *
     * @param string $format
     * @return float|string
     */
    public function getLatitude($format = Gps::FORMAT_DD)
    {
        return $this->getCoordinate($this->latitude, Gps::LATITUDE, $format);
    }

    /**
     * Retrieve formatted longitude
     *
     * @param string $format
     * @return float|string
     */
    public function getLongitude($format = Gps::FORMAT_DD)
    {
        return $this->getCoordinate($this->longitude, Gps::LONGITUDE, $format);
    }

    /**
     * Retrieve formatted coordinate value
     *
     * @param float $value
     * @param string $coordinate
     * @param string $format
     * @return float|string
     */
    protected function getCoordinate($value, $coordinate = Gps::LATITUDE, $format = Gps::FORMAT_DD)
    {
        switch ($format) {
            case Gps::FORMAT_DD:
                return sprintf('%s', round($value, 5));
                break;

            case Gps::FORMAT_DM:
                return $this->toDecimalMinutes($value, $coordinate);
                break;

            case Gps::FORMAT_DMS:
                return $this->toDegreesMinutesSeconds($value, $coordinate);
                break;
        }

        throw new \InvalidArgumentException(sprintf('Format "%s" is not valid', $format));
    }

    /**
     * Transform float coordinate to Decimal minutes
     *
     * @param float $value
     * @param string $coordinate
     * @return string
     */
    final protected function toDecimalMinutes($value, $coordinate = Gps::LATITUDE)
    {
        $degrees = intval(abs($value));
        $minutes = rtrim(round(abs($value) - floatval($degrees), 5), '0');
        $orientation = $coordinate === Gps::LATITUDE
            ? ($value < 0 ? 'S' : 'N')
            : ($value < 0 ? 'W' : 'E');

        return sprintf(
            '%d°%s%s',
            $degrees,
            $minutes === '' ? 0 : $minutes,
            $orientation
        );
    }

    /**
     * Transform float coordinate to Degrees minutes seconds
     *
     * @param float $value
     * @param string $coordinate
     * @return string
     */
    final protected function toDegreesMinutesSeconds($value, $coordinate = Gps::LATITUDE)
    {
        $degrees = intval(abs($value));
        $minutes = intval(abs($value) * 60) % 60;
        $seconds = round(fmod((abs($value) * 3600), 60), 5);
        $orientation = $coordinate === Gps::LATITUDE
            ? ($value < 0 ? 'S' : 'N')
            : ($value < 0 ? 'W' : 'E');

        return sprintf(
            '%d°%d\'%s"%s',
            $degrees,
            $minutes,
            $seconds,
            $orientation
        );
    }

    /**
     * Calculate distance to another point
     *
     * @param Point $point
     * @param string $unit
     * @return float
     */
    public function distanceTo(Point $point, $unit = Gps::KILOMETER)
    {
        $latitudeFrom  = deg2rad($this->latitude);
        $longitudeFrom = deg2rad($this->longitude);
        $latitudeTo    = deg2rad(floatval($point->getLatitude()));
        $longitudeTo   = deg2rad(floatval($this->getLongitude()));

        $longitudeDelta = $longitudeTo - $longitudeFrom;

        $longitudeDelta = $longitudeTo - $longitudeFrom;
        $magnitudeA = pow(cos($latitudeTo) * sin($longitudeDelta), 2) +
            pow(cos($latitudeFrom) * sin($latitudeTo) - sin($latitudeFrom) *
                cos($latitudeTo) * cos($longitudeDelta), 2);
        $magnitudeB = sin($latitudeFrom) * sin($latitudeTo) + cos($latitudeFrom) *
            cos($latitudeTo) * cos($longitudeDelta);

        $distance = atan2(sqrt($magnitudeA), $magnitudeB) * Gps::EARTH_RADIUS;

        switch ($unit) {
            case Gps::KILOMETER:
                return round($distance, 2);
                break;

            case Gps::METER:
                return round($distance * 1000, 2);
                break;

            default:
                throw new \InvalidArgumentException(sprintf('Unit "%s" is not valid', $unit));
                break;
        }
    }
}
