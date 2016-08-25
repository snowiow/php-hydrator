<?php

require_once '../vendor/autoload.php';

print '<pre>';

$doc = new DOMDocument('1.0', 'utf-8');
$doc->load('xml/get_response.xml');

//$np = new NodeProcessor($doc);
//print_r($np->getOutput());

final class XmlMapper
{
    private $output = [];

    public function __construct(DOMDocument $document)
    {
        $this->output = $this->traverse($document);
    }

    public function getOutput() : array
    {
        return $this->output;
    }

    private function traverse(DOMNode $node) : array
    {
        $output = [];
        /** @var DOMElement $child */
        foreach ($node->childNodes as $child) {
            switch ($child->nodeType) {
                case XML_ELEMENT_NODE:
                    $result = $this->traverse($child);
                    $result = $this->flatten($result);

                    $attributes = $this->extractAttributes($child);
                    if (!empty($attributes)) {
                        $result['@attributes'] = $attributes;
                    }

                    $output[$child->tagName][] = $result;
                    break;
                case XML_TEXT_NODE:
                    $output[] = utf8_decode($child->nodeValue);
                    break;
            }
        }

        return $this->flatten($output);
    }

    private function extractAttributes(DOMNode $node) : array
    {
        $output = [];
        /**
         * @var string      $attr
         * @var  DOMElement $value
         */
        foreach ($node->attributes as $attr => $value) {
            $output[$attr] = (string) $value->nodeValue;
        }

        return $output;
    }

    private function flatten(array $data) : array
    {
        foreach ($data as $key => $value) {
            if (is_array($value) && count($value) === 1) {
                $value = array_pop($value);
            }

            if (is_string($value)) {
                $value = trim($value);
            }

            if (empty($value)) {
                unset($data[$key]);
            } else {
                $data[$key] = $value;
            }
        }

        return $data;
    }
}

//
//$m = new XmlMapper($doc);
//print_r($m->getOutput());

final class XmlHydrator
{
    private $aliase = [];

    public function use (string $class) : Alias
    {
        return new Alias($this, $class);
    }

    public function setAlias(Alias $alias)
    {
        $this->aliase[$alias->getClass()] = $alias->getAlias();
    }

    public function hydrate(DOMDocument $document) : array
    {
        $mapper = new XmlMapper($document);

        return $this->hydrateArray($mapper->getOutput());
    }

    public function hydrateArray(array $values) : array
    {
        $objects = [];
        foreach ($values as $key => $value) {
            if (!is_array($value)) {
                continue;
            }

            $object = $this->hydrateClass($key, $value);
            if ($object !== null) {
                $objects[] = $object;
            } else {
                $objects = array_merge($objects, $this->hydrateArray($value));
            }
        }

        return $objects;
    }

    public function hydrateClass(string $class, array $values)
    {
        if (strpos($class, ':') !== false) {
            list(, $class) = explode(':', $class);
        }

        if (class_exists($class)) {
            return $this->hydrateObject(new ReflectionClass($class), $values);
        }

        return null;
    }

    public function hydrateObject(ReflectionClass $ref, array $values)
    {
        $hydrator = new ObjectHydrator($this, $ref);
        foreach ($values as $attr => $value) {
            $hydrator->assign($attr, $value);
        }

        return $hydrator->getObject();
    }
}

final class ObjectHydrator
{
    const SETTER_PREFIXES = ['set', 'add', 'append'];

    /**
     * @var XmlHydrator
     */
    private $hydrator;
    /**
     * @var ReflectionClass
     */
    private $reflection;
    /**
     * @var object
     */
    private $object;

    /**
     * ObjectHydrator constructor.
     *
     * @param XmlHydrator     $hydrator
     * @param ReflectionClass $reflection
     */
    public function __construct(XmlHydrator $hydrator, ReflectionClass $reflection)
    {
        $this->hydrator   = $hydrator;
        $this->reflection = $reflection;
        $this->object     = $reflection->newInstance();
    }

    /**
     * @return object
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * @param string $attribute
     * @param        $value
     *
     * @return bool
     */
    public function assign(string $attribute, $value)
    {
        if (is_array($value)) {
            $value = $this->hydrator->hydrateClass($attribute, $value) ?? $value;
        }

        if ($this->reflection->hasProperty($attribute) && $this->reflection->getProperty($attribute)->isPublic()) {
            $this->object->{$attribute} = $value;

            return true;
        }

        foreach (self::SETTER_PREFIXES as $prefix) {
            $method = $prefix . ucfirst($attribute);
            if (method_exists($this->object, $method)) {
                $this->object->{$method}($value);

                return true;
            }
        }

        return false;
    }
}

final class Alias
{
    /**
     * @var XmlHydrator
     */
    private $hydrator;
    /**
     * @var string
     */
    private $class;
    /**
     * @var string
     */
    private $alias;

    public function __construct(XmlHydrator $hydrator, string $class)
    {
        $this->hydrator = $hydrator;
        $this->class    = $class;
    }

    /**
     * @return XmlHydrator
     */
    public function getHydrator(): XmlHydrator
    {
        return $this->hydrator;
    }

    /**
     * @return string
     */
    public function getClass(): string
    {
        return $this->class;
    }

    /**
     * @return string
     */
    public function getAlias(): string
    {
        return $this->alias;
    }

    /**
     * @param string $alias
     */
    public function as (string $alias)
    {
        $this->alias = $alias;

        $this->hydrator->setAlias($this);
    }
}

$hydrator = new XmlHydrator();
//$hydrator->use('Foo')->as('Bar');
print_r($hydrator->hydrate($doc));

//function traverse(DOMNode $node, string $indent = '')
//{
//    $output = [];
//    /** @var DOMElement $child */
//    foreach ($node->childNodes as $child) {
//        switch ($child->nodeType) {
//            case XML_ELEMENT_NODE:
//                //                print $indent . ' ELEMENT ' . $child->tagName . PHP_EOL;
//                $result = traverse($child, $indent . "\t");
//                foreach ($result as $key => $value) {
//                    if (is_array($value)) {
//                        $c = count($value);
//                        if ($c === 1) {
//                            $value = array_pop($value);
//                        }
//                    } else if (is_string($value)) {
//                        $value = trim($value);
//                    }
//
//                    if (empty($value)) {
//                        unset($result[$key]);
//                    } else {
//                        $result[$key] = $value;
//                    }
//                }
//
//                $output[$child->tagName] = $result;
//                break;
//            case XML_TEXT_NODE:
//                //                print $indent . ' TEXT ' . utf8_decode($child->nodeValue) . PHP_EOL;
//                $output[] = utf8_decode($child->nodeValue);
//                break;
//            default:
//                //                print $indent . 'Irgendwas anderes' . PHP_EOL;
//        }
//    }
//
//    return $output;
//}

//print_r(traverse($doc));