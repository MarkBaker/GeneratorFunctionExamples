# A Functional Guide to Cat Herding with PHP Generators

When working with arrays in PHP, three of the most useful functions available to us are [array_map()](http://php.net/manual/en/function.array-map.php), [array_filter()](http://php.net/manual/en/function.array-filter.php) and [array_reduce()](http://php.net/manual/en/function.array-reduce.php), which allow us to walk an array and manipulate the value of array elements, select a subset of values from an array, or reduce an array to a single value; all using a callback function to determine exactly what logic should be applied. The use of the callback makes them extremely flexible, and these functions can be particularly powerful, especially when combined (or chained) together.

However, these functions only work with standard PHP arrays; so if we are using Generators as a data source instead of an array, then we can't take advantage of the functionality that they provide. Fortunately, it's very easy to emulate that functionality and apply it to Generators (and also to other Traversable objects like SPL Iterators), giving us access to all of the flexibility and power that mapping, filtering and reducing can offer.


Full working code examples demonstrating the functions used in this article are all available on [github](https://github.com/MarkBaker/GeneratorFunctionExamples).



## A real-world example of a Generator

Rather than the usual simplistic example Generators that are normally shown in blog posts and tutorials, I prefer to use a real-world example. In this case, a handler for reading `.gpx` files. A [GPX](http://www.topografix.com/gpx.asp) (or GPS eXchange format) file is an XML file format for storing coordinate data. It can store waypoints, tracks, and routes; and is commonly used by the GPS trackers worn by hikers and joggers. Those of my cats that spend a lot of their time out of doors are equipped with miniaturised trackers so that I can subsequently read the files and see where they've been, and discover their "favourite" haunts so that I know where they're most likely to be when I need to go out searching for them.

![Roman wearing his GPS Tracker](https://raw.githubusercontent.com/MarkBaker/GeneratorFunctionExamples/master/images/Roman%20and%20GPS%20Tracker.png)

__Roman wearing his GPS Tracker__

A typical GPX file (logging trackpoints) looks something like:

```
<?xml version="1.0" encoding="ISO-8859-1"?>
<gpx version="1.1" 
creator="Memory-Map 5.4.2.1089 http://www.memory-map.com"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns="http://www.topografix.com/GPX/1/1"
    xsi:schemaLocation="http://www.topografix.com/GPX/1/1 http://www.topografix.com/GPX/1/1/gpx.xsd">
    <trk>
        <name>Wythburn</name>
        <type>Track</type>
        <trkseg>
            <trkpt lat="54.5131924947" lon="-3.0448236664"><time>2015-03-02T07:59:35Z</time></trkpt>
            <trkpt lat="54.5131921768" lon="-3.0450893323"><time>2015-03-02T08:00:31Z</time></trkpt>
            <trkpt lat="54.5131534894" lon="-3.0448548317"><ele>192</ele><time>2015-03-02T08:00:51Z</time></trkpt>

...

            <trkpt lat="54.4399968465" lon="-2.9721705119"><ele>52</ele><time>2015-03-02T14:50:49Z</time></trkpt>
        </trkseg>
    </trk>
</gpx>
```

Not all GPS trackers record the timestamp (fortunately mine do), and occasionally they fail to log the elevation; but this isn't critical as we can provide default values when reading the file.

Trackpoints are logged every 10 seconds, so the files can grow to several hundred MB after a few hours; and I use a Generator that yields each trackpoint in turn, which means that I can then parse large GPX files without requiring excessive memory.

```
namespace GpxReader;

class GpxHandler {
    protected $gpxReader;

    public function __construct($gpxFilename) {
        $this->gpxReader = new \XMLReader();
        $this->gpxReader->open($gpxFilename);
    }

    public function getElements($elementType) {
        while ($this->gpxReader->read()) {
            if ($this->gpxReader->nodeType == \XMLREADER::ELEMENT &&
                $this->gpxReader->name == $elementType)
            {
                $doc = new \DOMDocument('1.0', 'UTF-8');
                $xml = simplexml_import_dom($doc->importNode($this->gpxReader->expand(), true));
                $gpxAttributes = $this->readAttributes($this->gpxReader);
                $gpxElement = $this->readChildren($xml);
                $gpxElement->position = $gpxAttributes;
 
               yield $gpxElement->timestamp => $gpxElement;
            }
        }
    }
}
```
*(Attribute and child parsers/formatters are stripped from the code above to keep it brief.)*

This Generator returns a `DateTime` object containing the timestamp value as the key, and a simple object with latitude, longitude and elevation properties as the value.


As a demonstration of this basic Generator:

```
$gpxReader = new GpxReader\GpxHandler($gpxFilename);

foreach ($gpxReader->getElements('trkpt') as $time => $element) {
    printf(
        '%s' . PHP_EOL . '    latitude: %7.4f longitude: %7.4f elevation: %d' . PHP_EOL,
        $time->format('Y-m-d H:i:s'),
        $element->position->latitude,
        $element->position->longitude,
        $element->elevation
    );
}
```
will output
```
2015-03-02 07:59:35
    latitude: 54.5132, longitude: -3.0448, elevation: 0
2015-03-02 08:00:31
    latitude: 54.5132, longitude: -3.0451, elevation: 0
2015-03-02 08:00:51
    latitude: 54.5132, longitude: -3.0449, elevation: 192

...

2015-03-02 14:50:39
    latitude: 54.4392, longitude: -2.9714, elevation: 52
2015-03-02 14:50:49
    latitude: 54.4400, longitude: -2.9722, elevation: 52
```



## Filtering the Data

This can generate a lot of data (a typical file will contain several thousands of trackpoints), but I can use a filter to reduce the number of data values returned by the Generator. Effectively, this is the same logic as PHP's [array_filter()](http://php.net/manual/en/function.array-filter.php) function, and allows me to retrieve a subset of the trackpoints based on a rule that's implemented as a callback function.

When I first started using Generators with PHP 5.5, my `filter()` function looked something like:

```
function isEmpty($value) {
    return !empty($value);
}

/**
 *  Version of filter to use with versions of PHP prior to 5.6.0,
 *  without the `$flag` option
 *  
 **/
function filter(Traversable $filter, Callable $callback = null) {
    if ($callback === null) {
        $callback = 'isEmpty';
    }

    foreach ($filter as $key => $value) {
        if ($callback($value)) {
            yield $key => $value;
        }
    }
}
```

Note that the `filter()` function is a Generator in its own right. When I use `filter()`, it takes on the responsibility of reading each entry in turn from the datasource Generator (the `Traversable`), executing the callback (the `Callable`) and deciding whether or not to yield that key/value back to the calling script based on the boolean response that it gets from the callback.

PHP 5.6.0 added a new `$flag` option to `array_filter()`, and I subsequently revised my `filter()` code to reflect this additional behaviour.

```
/**
 *  The `$flag` option (and the constants ARRAY_FILTER_USE_KEY and ARRAY_FILTER_USE_BOTH)
 *      were introduced in PHP 5.6.0
 *  
 **/
function filter(Traversable $filter, Callable $callback = null, $flag = 0) {
    if ($callback === null) {
        $callback = 'isEmpty';
    }

    foreach ($filter as $key => $value) {
        switch($flag) {
            case ARRAY_FILTER_USE_KEY:
                if ($callback($key)) {
                    yield $key => $value;
                }
                break;
            case ARRAY_FILTER_USE_BOTH:
                if ($callback($value, $key)) {
                    yield $key => $value;
                }
                break;
            default:
                if ($callback($value)) {
                    yield $key => $value;
                }
                break;
        }
    }
}
```

This was a particularly useful change, allowing me the option of filtering on the key returned by the Generator (my timestamp value as a DateTime object), so I can easily use the `ARRAY_FILTER_USE_KEY` flag with my filter to see where my cats were within a specific timeframe.

Checking where the cats were at a certain time is as easy as implementing a callback that checks the timestamp, returning a true/false if it does/doesn't fall within the specified date/time range.

```
$gpxReader = new GpxReader\GpxHandler($gpxFilename);

// Set the filter parameters
$startTime = new \DateTime('2015-03-02 13:20:00Z');
$endTime = new \DateTime('2015-03-02 13:30:00Z');
// Create the filter callback
$timeFilter = function($timestamp) use ($startTime, $endTime) {
    return $timestamp >= $startTime && $timestamp <= $endTime;
};

foreach (filter($gpxReader->getElements('trkpt'), $timeFilter, ARRAY_FILTER_USE_KEY) as $time => $element) {
    printf(
        '%s' . PHP_EOL . '    latitude: %7.4f longitude: %7.4f elevation: %d' . PHP_EOL,
        $time->format('Y-m-d H:i:s'),
        $element->position->latitude,
        $element->position->longitude,
        $element->elevation
    );
}
```

An alternative approach to filtering by time is to filter so that only every 2nd 3rd or even 4th trackpoint is returned, giving me a broad overview of the the route without all the detail; and where I can then zoom in on particular timeframes of interest, returning every trackpoint within that timeframe.


While filtering the data by time is my most common activity; the flexibility of using a callback does allow me to filter the trackpoints by other criteria. Using the `filter()` function with different callbacks allows me to check if the cats have ventured beyond the confines of a defined bounding box of lat/long coordinates; whether they have travelled more than 2 kilometers from the house; or even simply when they were inside the house while I was out at work.



## Using a "Map" to transform the Data

One thing that a gpx file doesn't record is the distance between successive trackpoints, so I use a `map()` function to add this new property to the Generator returned value object. This is the equivalent of PHP's [array_map()](http://php.net/manual/en/function.array-map.php) function.

```
function map(Callable $callback, Traversable $iterator) {
    foreach ($iterator as $key => $value) {
        yield $key => $callback($value);
    }
}
```

Like `filter()`, the `map()` function is also a Generator; and like `filter()`, it takes on the responsibility of reading each entry in turn from the datasource Generator (the `Traversable`), and executing the callback (the `Callable`) which is responsible for the actual "mapping" of the values, perhaps changing the actual structure of the value, or handling a change between WGS84 Latitude/Longitude to OSGB36 so that I can plot the route on Ordnance Survey maps which use that different [Geodetic Datum](https://en.wikipedia.org/wiki/Geodetic_datum), before yielding the key/value back to the calling script.

In this case, I have a little helper class for calculating distance (using the Haversine formula).
```
namespace GpxReader\Helpers;

class DistanceCalculator {
    public function setDistance(\GpxReader\GpxElement $point) {
        $point->distance = $this->calculateDistance($point);
        return $point;
    }
}
```
*(The actual distance calculation is stripped from the code above to keep it brief.)*

Calling the mapper then is as simple as

```
$gpxReader = new GpxReader\GpxHandler($gpxFilename);
$distanceCalculator = new GpxReader\Helpers\DistanceCalculator();

foreach (map([$distanceCalculator, 'setDistance'], $gpxReader->getElements('trkpt')) as $time => $element) {
    printf(
        '%s' . PHP_EOL . '    latitude: %7.4f longitude: %7.4f elevation: %d' . PHP_EOL .
            '    distance from previous point:  %5.2f m' . PHP_EOL,
        $time->format('Y-m-d H:i:s'),
        $element->position->latitude,
        $element->position->longitude,
        $element->elevation,
        $element->distance
    );
}
```
giving us an output that includes the newly "mapped" `distance` property
```
2015-03-02 07:59:35
    latitude: 54.5132 longitude: -3.0448 elevation: 0
    distance from previous point:   0.00 m
2015-03-02 08:00:31
    latitude: 54.5132 longitude: -3.0451 elevation: 0
    distance from previous point:  17.15 m
2015-03-02 08:00:51
    latitude: 54.5132 longitude: -3.0449 elevation: 192
    distance from previous point:  15.74 m

...

2015-03-02 14:50:39
    latitude: 54.4392 longitude: -2.9714 elevation: 52
    distance from previous point:  98.87 m
2015-03-02 14:50:49
    latitude: 54.4400 longitude: -2.9722 elevation: 52
    distance from previous point:  106.70 m
```

While this simple mapping callback doesn't provide much direct value in itself, it does allow me to calculate the total distance that they have travelled (and subsequently the average speed of movement) when combined with the `reduce()` function.

The fact that `filter()` and `map()` are both Generators means that I can chain them together, even combining several different filters and mappers in to a long chain where necessary.


Strictly speaking, PHP's `array_map()` function accepts multiple array arguments, and passes the elements from all of them the through to the callback function, to be processed "in parallel". I've kept my own `map()` implementation simple, working with only a single Traversable; but I also have a variant `mmap()` function (using SPL's MultipleIterator class) that accepts one or more Traversable arguments. This particular implementation also uses variadics, so it does require a minimum version 5.6 of PHP.

```
function mmap(Callable $callback, ...$iterators) {
    $mi = new MultipleIterator(MultipleIterator::MIT_NEED_ANY);
    foreach($iterators as $iterator) {
        $mi->attachIterator($iterator);
    }

    foreach($mi as $values) {
        yield $callback(...$values);
    }
}
```

## Reducing the Data 

When I plot the journey on OpenStreetMap or Google Maps, then I want to know the coordinates for the initial bounding box to display; and with an array I could use the [array_reduce()](http://php.net/manual/en/function.array-reduce.php) function () with a callback that works out the top and bottom latitudes, and the left and right longitudes for my bounding box. With the aid of a `reduce()` function, I can do something similar with the Generator.

```
function reduce(Traversable $iterator, Callable $callback, $initial = null) {
    $result = $initial;
    foreach($iterator as $value) {
        $result = $callback($result, $value);
    }
    return $result;
}
```

Because this is something I do fairly regularly, I have a helper class that can calculate the dimensions for me:

```
namespace GpxReader\Helpers;

class BoundingBox {
    function calculate($discard, \GpxReader\GpxElement $point) {
        $this->top = max($point->position->latitude, $this->top);
        $this->bottom = min($point->position->latitude, $this->bottom);
        $this->left = min($point->position->longitude, $this->left);
        $this->right = max($point->position->longitude, $this->right);

        return $this;
    }
}
```

As the values for the top, bottom, left and right properties of the bounding box are all maintained as properties in the helper class itself, I don't need to keep track of the running value in the callback arguments; but the `reduce()` callback function does require a `carry` value to match the arguments of the standard `array_reduce()` function. In this case, I simply allow the argument to be passed to the `BoundingBox::calculate()` method, and ignore it.

I run the bounding box check using the code shown below

```
$gpxReader = new GpxReader\GpxHandler($gpxFilename);
$boundaries = new GpxReader\Helpers\BoundingBox();

$boundingBox = reduce(
    $gpxReader->getElements('trkpt'),
    [$boundaries, 'calculate']
);

printf(
    'Top: %7.4f Bottom: %7.4f' . PHP_EOL .
        'Left: %7.4f Right: %7.4f' . PHP_EOL,
    $boundingBox->top,
    $boundingBox->bottom,
    $boundingBox->left,
    $boundingBox->right
);
```

which gives a result like:

```
Top: 54.5192 Bottom: 54.4338
Left: -3.0451 Right: -2.9628
```

Other callbacks that I use on a regular basis with reduce(), such as the one described below for calculating distance travelled do use the carry argument to maintain the running total as it iterates through the Generator values.

It's always nice to know how far may cats have travelled on their daily journeys (or that I've walked when I go out hiking), and again I use my `reduce()` function - combined with the mapper to add the distance between trackpoints - to calculate the total distance travelled. As long as I've used the mapper to add a distance property to the trackpoint data, I can then use `reduce()` with a callback that simply sums all those distance properties to give a total distance; but the order of the calls is important.

```
$gpxReader = new GpxReader\GpxHandler($gpxFilename);
$distanceCalculator = new GpxReader\Helpers\DistanceCalculator();

$totalDistance = reduce(
    map([$distanceCalculator, 'setDistance'], $gpxReader->getElements('trkpt')),
    function($runningTotal, $value) {
        $runningTotal += $value->distance;
        return $runningTotal;
    },
    0.0
);

printf(
    'Total distance travelled is %5.2f km' . PHP_EOL,
    $totalDistance / 1000
);
```

and running the code will give

```
Total distance travelled is 19.27 km
```

![Plot of the Example GPX Data on Google Maps](https://raw.githubusercontent.com/MarkBaker/GeneratorFunctionExamples/master/images/GpxTrackData1.png)

__Plot of the Example GPX Data on Google Maps__


I hasten to add that the sample data I've used with these examples isn't from any of my cats, but from a walk in the Lake District. Roman might have ranged 20km overnight in his younger days, but he's a "middle-aged" cat now and only manages about 8km in a typical day.



## Pulling a single property from the Generated value object

Finally, although it isn't something that I use regularly, I've implemented my own version of the [array_column()](http://php.net/manual/en/function.array-column.php) function to work with my Generator. This allows me to return a single property from the yielded object, optionally using another property as the returned key.

```
function column(Traversable $filter, $columnKey, $indexKey = null) {
    $numericKey = 0;
    foreach ($filter as $value) {
        $key = ($indexKey !== null) ? $value->$indexKey : $numericKey;
        yield $key => $value->$columnKey;
        ++$numericKey;
    }
}
```

The code logic here specifically works with objects as the yielded value, and doesn't have any error checking; but could easily be made more generic to test for the yielded datatype and retrieve the appropriate value and optionally key. 

So to retrieve only the elevation property from my Generator, I can use

```
foreach (column($gpxReader->getElements('trkpt'), 'elevation') as $key => $value) {
    echo $key, ' => ', $value, PHP_EOL;
}
```

## Summary

The map/filter/reduce pattern is one of the cornerstones of functional programming, and a useful feature that we can apply when working with arrays of data in PHP. Hopefully this article has shown how we can apply it to Generators with just a few simple functions; and demonstrated the power and flexibility of the pattern for more general use in our code, whether with Generators, or using the existing functions for arrays.

---

No cats were forced to walk anywhere that they didn't want to go during the writing of this article.
  