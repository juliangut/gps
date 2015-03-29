[![Build Status](https://travis-ci.org/juliangut/gps.svg?branch=master)](https://travis-ci.org/juliangut/gps)
[![Code Climate](https://codeclimate.com/github/juliangut/gps/badges/gpa.svg)](https://codeclimate.com/github/juliangut/gps)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/juliangut/gps/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/juliangut/gps/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/juliangut/gps/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/juliangut/gps/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/juliangut/gps/v/stable.svg)](https://packagist.org/packages/juliangut/gps)
[![Total Downloads](https://poser.pugx.org/juliangut/gps/downloads.svg)](https://packagist.org/packages/juliangut/gps)

# Juliangut GPS coordinates manipulation

GPS coordination handler and manipulation library.

## Installation

Best way to install is using [Composer](https://getcomposer.org/):

```
php composer.phar require juliangut/gps
```

Then require_once the autoload file:

```php
require_once './vendor/autoload.php';
```

## Usage

```php
// Create a point with or without coordinates
$gpsPoint = new Point();
$gpsPoint = new Point('48° 0.858277778N, 2°0.2945 E'); // Eiffel tower

// Set coordinates together
$gpsPoint->set('41.9, 12.5'); // Rome

// Set separated coordinates for Empire State Building
$this->point->setLatitude('40°44′ 54.3″N');
$this->point->setLongitude('73° 59′9″ W');

echo $gpsPoint->get(Point::FORMAT_DD); // Default if none especified
echo $gpsPoint->get(Point::FORMAT_DM);
echo $gpsPoint->get(Point::FORMAT_DMS);
```

### Available formats

Any of the following formats can be used to set and retrieve GPS coordinates:

* Decimal Degrees (DD) `Point::FORMAT_DD`, eg: '41.9,12.5'
* Decimal Minutes (DM) `Point::FORMAT_DM`, eg: '48°0.858277778N 2°0.2945E'
* Degrees Minutes Seconds (DMS) `Point::FORMAT_DMS`, eg: '40°44′54.3″N,73°59′9″W'

### Considerations

When setting coordinates spaces and comma are completely optional, there is no difference between , `40° 44′ 54.3″ N, 73° 59′ 9″ W` and `40°44′54.3″N73°59′9″W`

When setting coordinates you can use `′` or `'` for minutes and `″` or `"` for seconds (review raw document to see the difference between them)

When retrieving coordinates won't have any spaces

When retrieving coordinates `'` and `"` will be used for minutes and seconds. Coordinates will be comma separated

## License

### Release under BSD-3-Clause License.

See file [LICENSE](https://github.com/juliangut/gps/blob/master/LICENSE) included with the source code for a copy of the license terms

