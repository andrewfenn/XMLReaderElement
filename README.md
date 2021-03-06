XMLReaderElement extension for Sabre/XML
=========

[![Software License](https://img.shields.io/badge/license-BSD-brightgreen.svg?style=flat-square)](LICENSE)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/andrewfenn/XMLReaderElement/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/andrewfenn/XMLReaderElement/?branch=master)
[![Total Downloads](https://img.shields.io/packagist/dd/andrewfenn/xmlreader.svg?style=flat-square)](https://packagist.org/packages/andrewfenn/xmlreader)
[![Total Downloads](https://img.shields.io/packagist/dm/andrewfenn/xmlreader.svg?style=flat-square)](https://packagist.org/packages/andrewfenn/xmlreader)
[![Total Downloads](https://img.shields.io/packagist/dt/andrewfenn/xmlreader.svg?style=flat-square)](https://packagist.org/packages/andrewfenn/xmlreader)

The sabre/xml library is a specialized XML reader and writer for PHP which can be downloaded [here](http://sabre.io/xml/). This project is not associated with the sabre/xml project.

Sabre XML is a great PHP library for XML reading, but it can be difficult to use and make your code unessesarily complicated when you have very simple XML to parse. Therefore I have made this small extension on top of sabre/xml that allows you to access the information more natually making it easier to get what you want done.

## Install

```composer require andrewfenn/xmlreader -v ~1.0.0```


## Methods

```XMLReaderElement``` provides the following methods...

* **```array find( string )```**

  Searches through all the children or attributes, and returns an array. If you are searching for a tag the array will be a list of XMLReaderElement elements. If you are searching for an attribute your array will be of the value it contains. To search for an attribute prepend an @ to the beginning of your search term.

* **```mixed findFirst( string )```**

  Searches through all the children or attributes and returns the first result found. If you are searching for a tag the result will be an XMLReaderElement. If you are searching for an attribute you will get that attributes value.

* **```array children( void )```**

  Returns an array of this XMLReaderElement's children.

* **```bool hasChildren( void )```**

  If the element has any XMLReaderElement children

## Examples

```php
$input = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<HotelMessage Version="1.0"
              TimeStamp="2016-05-26T11:11:50">
    <Message>Hello</Message>
    <Hotel HotelCode="3">
        <AvailableStatus>
            <StatusControl Start="2017-01-01" End="2017-01-02" InvTypeCode="999" RatePlanCode="888" />
            <LengthsOfStay ArrivalDateBased="true">
                <LengthOfStay MinMaxMessageType="MinLOS" TimeUnit="Day" Time="3" />
            </LengthsOfStay>
        </AvailableStatus>
    </Hotel>
    <Hotel HotelCode="7">
        <AvailableStatus>
            <StatusControl Start="2017-01-04" End="2017-01-05" InvTypeCode="111" RatePlanCode="444" />
            <LengthsOfStay ArrivalDateBased="true">
                <LengthOfStay MinMaxMessageType="MinLOS" TimeUnit="Day" Time="7" />
            </LengthsOfStay>
        </AvailableStatus>
    </Hotel>
</HotelMessage>
XML;

// Use Sabre as usual
$reader = new \Sabre\Xml\Reader();
$reader->xml($input);

// Parse in the reader object
$data = (new \Sabre\Xml\XMLReaderElement())->parse($reader->parse());
```

### Debugging
```php
// Start accessing the data you want
var_dump($data);

/* Returns...
object(Sabre\Xml\XMLReaderElement)#68 (4) {
  ["name"]=>
  string(12) "HotelMessage"
  ["namespace"]=>
  string(0) ""
  ["attributes"]=>
  object(stdClass)#69 (2) {
    ["Version"]=>
    string(3) "1.0"
    ["TimeStamp"]=>
    string(19) "2016-05-26T11:11:50"
  }
  ["children"]=>
  string(19) "Message,Hotel,Hotel"
}
*/
```
### Accessing a single child tag

```php
echo "Message: ".$data->Message->value."\n";
echo "Message Type: ".$data->Message->attributes->Type."\n";

/* Returns...
Message: Hello
Message Type: Info
*/
```
When accessing a single child tag like this it will return the first element it finds, for multiple children see below.

```php
echo "Hotel ID: ".$data->Hotel->attributes->HotelCode."\n";
/* Returns...
Hotel ID: 3
*/

```

### Accessing multiple children tags
```php
foreach($data->find('Hotel') as $hotel) {
    // Find a specific element's attribute
    echo "Hotel ID: ".$hotel->attributes->HotelCode."\n";
}

foreach($data->children() as $tag) {
    if ($tag->name == 'Hotel') {
        echo "Hotel ID: ".$tag->attributes->HotelCode."\n";
    }
}

/* Returns...
Hotel ID: 3
Hotel ID: 7
Hotel ID: 3
Hotel ID: 7
*/
```

### Finding an array of tags or attributes

The ```find()``` function will always return an array. Your search term must match exactly to that of the Tag or Attribute.

```php
// You can search for any tag attribute by prepending an @ infront of the attribute's name like so...
foreach($data->find('@HotelCode') as $hotel_code) {
    echo "Hotel Code: ".$hotel_code."\n";
}

// You can search for any tag like so...
foreach($data->find('LengthsOfStay') as $los) {
    echo "Length of Stay: ".$los->findFirst('@Time')." Days\n";
}

/* Returns...
Hotel Code: 3
Hotel Code: 4
Length of Stay: 3 Days
Length of Stay: 7 Days
*/
```

### Picking up the first element or attribute
```php
echo $data->findFirst('@TimeStamp')."\n";
echo $data->findFirst('@HotelCode')."\n";
echo $data->findFirst('Hotel')->findFirst('@RatePlanCode')."\n";

/* Returns...
2016-05-26T11:11:50
3
888
*/
```
