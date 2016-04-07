<?php

namespace GpxReader;

class GpxHandler {
    /**
     *  @var    \XMLReader    $gpxReader    XML Reader
     **/
    protected $gpxReader;

    /**
     *  Instantiate our GPX handler, and load the specified filename
     *
     *  @param   string  $gpxFilename  Load the specified filename into our GPX handler
     *  @throws  \Exception  Unable to load the specified file
     **/
    public function __construct($gpxFilename) {
        if (!file_exists($gpxFilename)) {
            throw new \Exception(sprintf('File "%s" does not exist', $gpxFilename));
        }

        $this->gpxReader = new \XMLReader();
        $this->gpxReader->open($gpxFilename);
    }

    /**
     *  Map an attribute of the current element to a property of the GpxLatLong object
     *
     *  @param  string                 $attributeName   The name of the attribute
     *  @param  string                 $attributeValue  The value of the attribute
     *  @param  \GpxReader\GpxLatLong  $gpxLatLong      The GpxLatLong object whose properties we want to set 
     **/
    protected function mapAttributes($attributeName, $attributeValue, GpxLatLong $gpxLatLong) {
        switch($attributeName) {
            case 'lat':
                $gpxLatLong->latitude = (float) $attributeValue;
                break;
            case 'lon':
                $gpxLatLong->longitude = (float) $attributeValue;
                break;
        }
    }

    /**
     *  Read the attributes of the current element, and restructure them into a GpxLatLong object
     *
     *  @param   \XMLReader  $xmlReader  The XMLReader object pointing to the current element whose attributes we want to reformat as an object
     *  @return  \GpxReader\GpxLatLong
     **/
    protected function readAttributes(\XMLReader $xmlReader) {
        $attributes = new GpxLatLong();
        if ($xmlReader->hasAttributes)  {
            while($xmlReader->moveToNextAttribute()) {
                $this->mapAttributes($xmlReader->name, $xmlReader->value, $attributes);
            }
        }
        return $attributes;
    }

    /**
     *  Map the child elements of the current element, to the properties of the GpxElement object
     *
     *  @param   \SimpleXMLElement      $element     The element that we want to reformat as an object
     *  @return  \GpxReader\GpxElement  $gpxElement  The object that we want to set the properties for
     **/
    protected function mapChildElements($element, GpxElement $gpxElement) {
        switch($element->getName()) {
            case 'ele':
                $gpxElement->elevation = (float) $element->__toString();
                break;
            case 'time':
                $gpxElement->timestamp = new \DateTime($element->__toString());
                break;
        }
    }

    /**
     *  Read the child elements of the current element, and restructure them into a GpxElement object
     *
     *  @param   \SimpleXMLElement  $element  The element that we want to reformat as an object
     *  @return  \GpxReader\GpxElement
     **/
    protected function readChildren(\SimpleXMLElement $element) {
        $gpxElement = new GpxElement();
        foreach($element->children() as $child) {
            $this->mapChildElements($child, $gpxElement);
        }
        return $gpxElement;
    }

    /**
     *  Generator to read each specified element in turn and return it as a nice object
     *
     *  @param   string  $elementType  The element type that we want to return
     *  @return  \Generator  \DateTime => \GpxReader\GpxElement
     **/
    public function getElements($elementType) {
        while ($this->gpxReader->read()) {
            if ($this->gpxReader->nodeType == \XMLREADER::ELEMENT && $this->gpxReader->name == $elementType) {
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
