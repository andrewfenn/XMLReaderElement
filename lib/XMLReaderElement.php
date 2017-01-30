<?php
namespace Sabre\Xml;

class XMLReaderElement implements \Iterator {

    protected $namespace;
    protected $name;
    protected $attributes;
    protected $value;

    public function rewind() {
        reset($this->value);
    }

    public function current() {
        return current($this->value);
    }

    public function key() {
        return key($this->value);
    }

    public function next() {
        return next($this->value);
    }

    public function valid() {
        $key = key($this->value);
        return ($key !== NULL && $key !== FALSE);
    }

    public function parse($data) {
        $this->parseNameSpace($data);
        $this->attributes = (object) $this->convertAttributes($data['attributes']);

        if ($this->isElementArray($data['value'])) {
            $this->value = [];

            foreach ($data['value'] as $value) {
                $this->value[] = (new XMLReaderElement())->parse($value);
            }
        } elseif ($this->isElementValue($data['value'])) {
            $this->value = $this->convertValue($data['value']);
        } else {
            $this->value = (new XMLReaderElement())->parse($data['value']);
        }

        return $this;
    }

    protected function isElementArray($value) {
        return is_array($value) && !array_key_exists('name', $value) && array_key_exists('name', current($value));
    }

    protected function isElementValue($value) {
        return !is_array($value) || !array_key_exists('name', $value);
    }

    protected function parseNameSpace($data) {
        $namespace = [];
        preg_match_all('/{(.*?)}/', $data['name'], $namespace);

        if (count($namespace) === 2) {
            $this->namespace = $namespace[1][0];
            $this->name      = str_replace($namespace[0][0], '', $data['name']);
        } else {
            $this->name      = $data['name'];
        }
    }

    protected function convertAttributes($attributes) {
        foreach ($attributes as $k=>$attribute) {
            $attributes[$k] = $this->convertValue($attribute);
        }

        return $attributes;
    }

    protected function convertValue($value) {
        if (!is_string($value)) {
            return $value;
        }

        if ($this->isBool($value)) {
            return filter_var($value, FILTER_VALIDATE_BOOLEAN);
        }

        if ($this->isInteger($value)) {
            return (int) $value;
        }

        return $value;
    }

    /* Very specific type of integer checking to ensure
    that we have a number value, and not one that has been
    mistakenly casted by PHP. Examples below.

    var_dump(isInteger(23));    //bool(true)
    var_dump(isInteger("23"));  //bool(true)
    var_dump(isInteger(23.5));  //bool(false)
    var_dump(isInteger(NULL));  //bool(false)
    var_dump(isInteger(""));    //bool(false)
    */
    protected function isInteger($input) {
        return (ctype_digit(strval($input)));
    }

    /* Very specific type of boolean checking to ensure
    that we have a bool value, and not one that has been
    mistakenly casted by PHP. Examples below.

    var_dump(isBool(true));     //bool(true)
    var_dump(isBool("false"));  //bool(true)
    var_dump(isBool(0));        //bool(false)
    var_dump(isBool(NULL));     //bool(false)
    var_dump(isBool(""));       //bool(false)
    */
    protected function isBool($input) {
        return in_array(strtolower($input), ['true', 'false']) !== false;
    }

    public function children() {
        if ($this->value instanceof XMLReaderElement) {
            return [$this->value];
        }

        if (is_array($this->value)) {
            $results = [];
            foreach ($this->value as $value) {
                if ($value instanceof XMLReaderElement) {
                    $results[] = $value;
                }
            }
            return $results;
        }

        return [];
    }

    public function hasChildren() {
        return !empty($this->children());
    }

    public function findFirst($search) {
        return current($this->find($search));
    }

    public function find($search) {

        $results = [];

        if ($this->name == $search) {
            $results[] = $this;
        }

        foreach ($this->children() as $child) {
            $results = array_merge($results, $child->find($search));
        }

        if ($search[0] == '@') {
            $search = substr($search, 1);
            $results = array_merge($results, $this->findAttribute($search));
        }

        return $results;
    }

    protected function findAttribute($search) {

        $results = [];
        if (property_exists($this->attributes, $search)) {
            $results[] = $this->attributes->$search;
        }

        return $results;
    }

    public function __get($name) {
        /* Access the Elements Attributes */
        if (!is_array($this->value)) {
            return $this->$name;
        }

        foreach ($this->value as $value) {
            if ($value instanceof XMLReaderElement && $value->name == $name) {
                return $value;
            }
        }

        return $this->$name;
    }

    public function __debugInfo() {

        $arr = ['name'       => $this->name,
                'namespace'  => $this->namespace,
                'attributes' => $this->attributes
                ];

        if ($this->hasChildren()) {
            $names = [];
            foreach ($this->children() as $child) {
                $names[] = $child->name;
            }

            $arr += ['children' => implode(',', $names)];
        } else {
            $arr += ['value' => $this->value];
        }

        return $arr;
    }
}
