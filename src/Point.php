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

    const DECIMAL_DEGREES         = 'decimal_degrees';
    const DECIMAL_MINUTES         = 'decimal_minutes';
    const DEGREES_MINUTES_SECONDS = 'degrees_minutes_seconds';

    /**
     * Format for decimal degrees coordinates
     *
     * @var string
     */
    protected $decimalDegreesFormat         = '-?\d+(\.\d+)?';

    /**
     * Format for decimal minutes coordinates
     *
     * @var string
     */
    protected $decimalMinutesFormat         = '(\d+)°(\d+(\.\d+)?)([NSEW])';

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

        $this->setCoordinate($coordinates[0], self::LATITUDE);
        $this->setCoordinate($coordinates[1], self::LONGITUDE);

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

        return array_map(
            function ($component) {
                return $this->normalizeCoordinate($component);
            },
            $coordinates
        );
    }

    /**
     * Set latitude coordinate
     *
     * @param mixed $latitude
     * @return $this
     */
    public function setLatitude($latitude)
    {
        $this->setCoordinate($this->normalizeCoordinate($latitude), self::LATITUDE);

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
        $this->setCoordinate($this->normalizeCoordinate($longitude), self::LONGITUDE);

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
    protected function setCoordinate($value, $coordinate = self::LATITUDE)
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

        if ($coordinate === self::LATITUDE) {
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
    final protected function validateCoordinateOrientation($orientation, $coordinate = self::LATITUDE)
    {
        if ($coordinate === self::LATITUDE && !in_array($orientation, array('N', 'S'))
            || $coordinate === self::LONGITUDE && !in_array($orientation, array('E', 'W'))
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
    public function get($format = self::DECIMAL_DEGREES)
    {
        return sprintf('%s,%s', $this->getLatitude($format), $this->getLongitude($format));
    }

    /**
     * Retrieve formatted latitude
     *
     * @param string $format
     * @return float|string
     */
    public function getLatitude($format = self::DECIMAL_DEGREES)
    {
        return $this->getCoordinate($this->latitude, self::LATITUDE, $format);
    }

    /**
     * Retrieve formatted longitude
     *
     * @param string $format
     * @return float|string
     */
    public function getLongitude($format = self::DECIMAL_DEGREES)
    {
        return $this->getCoordinate($this->longitude, self::LONGITUDE, $format);
    }

    /**
     * Retrieve formatted coordinate value
     *
     * @param string $coordinate
     * @param string $format
     * @return float|string
     */
    protected function getCoordinate($value, $coordinate = self::LATITUDE, $format = self::DECIMAL_DEGREES)
    {
        switch ($format) {
            case self::DECIMAL_DEGREES:
                return sprintf('%s', round($value, 5));
                break;

            case self::DECIMAL_MINUTES:
                return $this->toDecimalMinutes($value, $coordinate);
                break;

            case self::DEGREES_MINUTES_SECONDS:
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
    final protected function toDecimalMinutes($value, $coordinate = self::LATITUDE)
    {
        $degrees = intval(abs($value));
        $minutes = rtrim(round(abs($value) - floatval($degrees), 5), '0');
        $orientation = $coordinate === self::LATITUDE
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
    final protected function toDegreesMinutesSeconds($value, $coordinate = self::LATITUDE)
    {
        $degrees = intval(abs($value));
        $decimalMinutes = (abs($value) - floatval($degrees)) * 60;
        $minutes = intval($decimalMinutes);
        $seconds = round(($decimalMinutes - floatval($minutes)) * 60, 5);
        if (intval($seconds) === 60) {
            $minutes++;
            $seconds = 0;
        }
        $seconds = rtrim($seconds, '0');
        $orientation = $coordinate === self::LATITUDE
            ? ($value < 0 ? 'S' : 'N')
            : ($value < 0 ? 'W' : 'E');

        return sprintf(
            '%d°%d\'%s"%s',
            $degrees,
            $minutes,
            $seconds === '' ? 0 : $seconds,
            $orientation
        );
    }
}
