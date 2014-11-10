<?php

//@todo show if variable is a reference (dont know how i can check this atm)

namespace Dumper;

/**
 * Class Dumper
 * @copyright Julius Horn <juliusjuliushornhorn@gmail.com>
 */
class Dumper
{
    const TYPE_STRING   = 0;
    const TYPE_OBJECT   = 1;
    const TYPE_ARRAY    = 2;
    const TYPE_NUMBER   = 3;
    const TYPE_NULL     = 4;
    const TYPE_BOOL     = 5;
    const TYPE_FUNCTION = 6;
    const TYPE_RESOURCE = 7;

    const PROP_PRIVATE   = 'private';
    const PROP_PROTECTED = 'protected';
    const PROP_PUBLIC    = 'public';

    const CHAR_SPACE       = '&nbsp';
    const STRING_RECURSIVE = 'REKURSION';

    /**
     * @var bool
     */
    protected $hidden     = false;
    protected $detailed   = true;
    protected $reflection = false;

    /**
     * @var $this
     */
    protected static $ini;

    /**
     * @var string
     */
    protected $arrayHash;

    /**
     * Generate unique?! Array-Recursion Hash key
     */
    private function __construct()
    {
        $this->arrayHash = '__udk_' . md5(uniqid());
    }

    /**
     * @return Dumper
     */
    public static function getInstance()
    {
        if (!self::$ini) {
            self::$ini = new self;
        }

        return self::$ini;
    }

    /**
     * @param $data
     * @param bool $hidden
     * @param bool $detailed
     */
    public static function dump(&$data, $hidden = false, $detailed = true)
    {
        self::getInstance()->dumpExec($data, $hidden);
    }

    /**
     * @param $data
     * @param $hidden
     * @param bool $detailed
     */
    public static function dumpReflection(&$data, $hidden = false, $detailed = true)
    {
        self::getInstance()->dumpExec($data, $hidden, $detailed, true);
    }

    /**
     * @param $data
     * @param bool $hidden
     * @param bool $detailed
     * @param bool $reflection
     */
    public function dumpExec(&$data, $hidden = false, $detailed = true, $reflection = false)
    {
        //ob_start();

        $this->detailed   = $detailed;
        $this->hidden     = $hidden;
        $this->reflection = $reflection;

        $this->printCss();
        $this->dumpTrace();
        $this->dumpChild($data, 0);
        $this->printJs();

        if ($hidden) {
            $this->printHidden();
        }

        //$view = ob_get_clean();
        //echo $view;
    }

    /**
     * Information about file where dumped
     */
    private function dumpTrace()
    {
        $bt = debug_backtrace();

        if (isset($bt[2])) {
            $file = pathinfo($bt[2]['file']);
            $line = $bt[2]['line'];

            echo '<div id="dumper-trace-stack" class="dumper-trace-stack">';

            echo '<p class="dumper-trace-header">';
            echo '<button id="dumper-spoiler" style="float:right">show excerpt</button>';
            echo '<span>Trace: </span>';
            echo '<span class="dumper-trace-path">' . $file['dirname'] . '/</span>';
            echo '<span class="dumper-trace-file">' . $file['basename'] . '</span>';
            echo ' <span class="dumper-trace-line">(line ' . $line . ')</span>';
            echo '</p>';

            $range = 2;

            $begin   = $line - 1 - $range;
            if ($begin < 0) {
                $begin = 0;
            }

            echo '<div class="dumper-trace-codeblock">';
            $content = explode("\n", file_get_contents($bt[2]['file']));

            for ($i = $begin; $i < $line + $range; $i++) {
                if (isset($content[$i])) {
                    $class = '';

                    if ($i == $line - 1) {
                        $class = ' dumper-trace-highlight';
                    }

                    echo '<span class="dumper-trace-sub-line' . $class. '">';
                    echo $i + 1;
                    echo '</span>';

                    echo '<span class="dumper-trace-code' . $class . '">';
                    echo str_replace(' ', self::CHAR_SPACE, $this->quote($content[$i]));
                    echo '</span><br />';
                }
            }
            echo '</div>';
            echo '</div>';


        }
    }

    /**
     * @return void
     */
    private function printHidden()
    {
        echo '<script>$(\'.dumper-sub\').hide()</script>';
    }

    /**
     * @return void
     */
    private function printCss()
    {
        echo '<style>' . file_get_contents(__DIR__ . '/dumper-style.css') . '</style>';

    }

    /**
     * @return void
     */
    private function printJs()
    {
        echo '<script>' . file_get_contents(__DIR__ . '/jquery.js') . '</script>';
        echo '<script>' . file_get_contents(__DIR__ . '/dumper-script.js') . '</script>';
    }

    /**
     * @param $child
     * @param $depth
     * @param array $parentObjs
     */
    private function dumpChild(&$child, $depth, $parentObjs = array())
    {
        $type = $this->getType($child);

        switch ($type) {
            case self::TYPE_NULL :
                $this->printNull();
                break;
            case self::TYPE_ARRAY :
                $this->printArray($child, $depth + 1, $parentObjs);
                break;
            case self::TYPE_STRING :
                $this->printString($child, $depth);
                break;
            case self::TYPE_OBJECT :
                $this->printObject($child, $depth, $parentObjs);
                break;
            case self::TYPE_NUMBER :
                $this->printNumber($child, $depth);
                break;
            case self::TYPE_BOOL :
                $this->printBool($child);
                break;
            case self::TYPE_FUNCTION :
                $this->printFunction($child);
                break;
            case self::TYPE_RESOURCE :
                $this->printResource($child);
        }
    }

    /**
     * @param $function
     */
    private function printFunction($function)
    {
        echo '<span class="dumper-function">function()</span><br />';
    }

    /**
     * @param $bool
     */
    private function printBool($bool)
    {
        echo '<span class="dumper-type">bool</span>' . self::CHAR_SPACE;

        if ($bool) {
            echo '<span class="dumper-true">true</span><br />';
        } else {
            echo '<span class="dumper-false">false</span><br />';
        }
    }

    /**
     * @param $res
     */
    private function printResource($res)
    {
        $open  = ' <span class="dumper-resource">[</span>';
        $close = '<span class="dumper-resource">]</span>';

        $add  = ''; //default spec... kp
        $type = get_resource_type($res);

        if ($type == 'stream') {
            $metaData = stream_get_meta_data($res);
            $spec     = '<span class="dumper-count">' . $metaData['wrapper_type'] . '</span>';
            $add      = $open . '<span>' . $metaData['uri'] . '</span>' . $close;
        } elseif ($type == 'curl') {
            $info = curl_getinfo($res);
            $add  = $open . '<span class="dumper-count">' . $info['url'] . '</span>' . $close;
        }

        echo '<span class="dumper-type">resource</span> ';
        echo '<span class="dumper-resource">' . $type . '</span>';
        echo $add;

        echo '<br />';
    }

    /**
     * @return void
     */
    private function printNull()
    {
        echo '<span class="dumper-null">null</span><br />';
    }

    /**
     * @param $number
     */
    private function printNumber($number)
    {
        $type = gettype($number);

        if ($type == 'integer') {
            $type = 'int';
        }

        echo '<span class="dumper-type">' . $type . '</span>';
        echo self::CHAR_SPACE . '<span class="dumper-number">' . $number . '</span><br />';
    }

    /**
     * @param \ReflectionMethod $rm
     * @return string
     */
    private function getAccess(\ReflectionMethod $rm)
    {
        $access = self::PROP_PRIVATE;

        if ($rm->isPublic()) {
            $access = self::PROP_PUBLIC;
        } elseif ($rm->isProtected()) {
            $access = self::PROP_PROTECTED;
        }

        return $access;
    }

    /**
     * @param \ReflectionMethod $rm
     * @return array
     */
    private function getMethodParams(\ReflectionMethod $rm)
    {
        $result = array();
        $params = $rm->getParameters();

        foreach ($params as $param) {
            $result[] = '$' . $param->name;
        }

        return $result;
    }

    /**
     * @param $obj
     * @param $depth
     * @param $parentObjs
     */
    private function printObject($obj, $depth, $parentObjs)
    {
        $hash = spl_object_hash($obj);
        if (in_array($hash, $parentObjs)) {
            echo '<a class="dumper-recursive" href="#obj-id' . $hash . '">' . self::STRING_RECURSIVE . ' (' . get_class($obj) . ')</a><br />';
        } else {
            $parentObjs[] = $hash;
            $inactive = '';

            if ($this->hidden) {
                $inactive = 'inactive';
            }

            $class = get_class($obj);

            echo '<span id="obj-id' . $hash . '" class="dumper-sub-trigger ' . $inactive . '">';
            echo '<span class="dumper-important-type">object</span>';
            echo ' (' . $class . ')</span>';
            echo '<br /><div class="dumper-sub">';


            if ($this->reflection) {
                $mRes    = array();
                $refObj  = new \ReflectionObject($obj);
                $methods = $refObj->getMethods();

                foreach ($methods as $method) {
                    $m   = array(
                        'name'    => $method->name,
                        'class'   => $method->class,
                        'options' => $this->getMethodOptions($method)
                    );

                    $mRes[] = $m;
                }

                if ($this->detailed) {
                    $mRes = $this->orderAccessibility($mRes);
                }

                $this->printMethods($mRes, $depth + 1);
            }

            $vars = (array) $obj;
            foreach ($vars as $key => &$val) {
                $meta = $this->getPropType($key, $class);
                $this->printSpace($depth + 1);
                echo '<span class="dumper-obj-key"><span class="dumper-type">' . $meta['type'] . '</span>';
                echo self::CHAR_SPACE . $meta['key'] . '</span>';
                $this->printEqual('=');
                $this->dumpChild($val, $depth + 1, $parentObjs);
            }

            echo '</div>';
        }
    }

    /**
     * @param array $methods
     * @return array
     */
    private function orderAccessibility(&$methods)
    {
        $private   = array();
        $protected = array();
        $public    = array();

        foreach ($methods as &$method) {
            switch ($method['options']['access']) {
                case self::PROP_PRIVATE :
                    $private[] = &$method;
                    break;
                case self::PROP_PROTECTED :
                    $protected[] = &$method;
                    break;
                case self::PROP_PUBLIC :
                    $public[] = &$method;
                default :
                    //do nothing
            }
        }

        return array_merge($private, $protected, $public);
    }

    /**
     * @param \ReflectionMethod $rm
     * @return array
     */
    private function getMethodOptions(\ReflectionMethod $rm)
    {
        $result = array();

        $result['access'] = $this->getAccess($rm);
        $result['params'] = $this->getMethodParams($rm);
        $add    = array();

        if ($rm->isFinal()) {
            $add[] = 'final';
        }

        if ($rm->isStatic()) {
            $add[] = 'static';
        }

        if (empty($add)) {
            $result['add'] = '';
        } else {
            $result['add'] = ' ' . implode(' ', $add);
        }


        return $result;
    }

    /**
     * @param $methodCollection
     * @param $depth
     */
    private function printMethods($methodCollection, $depth)
    {
        $this->printSpace($depth);

        echo '<span class="dumper-methods">Class: </span>';

        $inactive = '';

        if ($this->hidden) {
            $inactive = 'inactive';
        }

        echo '<span class="dumper-sub-trigger ' . $inactive . '">';
        echo '<span> Methods (' . count($methodCollection) . ')</span>';
        echo '</span><br /><div class="dumper-sub">';

        foreach ($methodCollection as $m) {
            echo '<p>';
            $this->printSpace($depth + 1);
            echo '<span class="dumper-type">' . $m['options']['access'] . $m['options']['add'] . '</span> ';
            echo '<span class="">' . $m['class'] . '</span>';
            echo '<span class="dumper-equal">::</span>';
            echo '<span class="dumper-function">' . $m['name'] . '</span>(';
                $this->printParams($m['options']['params']);
            echo ')';
            echo '</p>';
        }

        echo '</div>';
    }

    /**
     * @param $params
     */
    private function printParams($params)
    {
        $i = 0;
        foreach ($params as $p) {
            if ($i !== 0) {
                echo ', ';
            }

            echo '<span class="dumper-params">' . $p . '</span>';

            $i++;
        }
    }

    /**
     * @param $prop
     * @param $class
     * @return string
     */
    private function getPropType($prop, $class)
    {
        /*
         * \0*\0         = protected
         * \0ClassName\0 = private
         * std           = public
         */

        $result = array('type' => self::PROP_PUBLIC, 'key' => $prop);
        $parts  = explode("\0", $prop);

        if (isset($parts[1]) && $parts[1] == '*') {
            $result['type'] = self::PROP_PROTECTED;
            $result['key']  = $parts[2];
        } elseif (isset($parts[1]) && $parts[1] == $class) {
            $result['type'] = self::PROP_PRIVATE;
            $result['key']  = $parts[2];
        }

        return $result;
    }

    /**
     * @param $array
     * @param $depth
     * @param $parentObjs
     */
    private function printArray(&$array, $depth, $parentObjs)
    {
        //rekusrion
        if (isset($array[$this->arrayHash])) {
            echo '<span class="dumper-recursive">' . self::STRING_RECURSIVE . ' (array)</span><br />';
        } else {
            $inactive = '';

            if ($this->hidden) {
                $inactive = 'inactive';
            }

            echo '<span class="dumper-sub-trigger ' . $inactive . '">';
            echo '<span class="dumper-important-type">array</span>';
            echo self::CHAR_SPACE . '(<span class="dumper-count">' . count($array) . '</span>)</span>';
            echo '<br /><div class="dumper-sub">';

            $printKey = null;
            $keyClass = null;

            $array[$this->arrayHash] = true;
            foreach ($array as $key => &$val) {
                if ($key == $this->arrayHash) {
                    unset($array[$this->arrayHash]);
                    break;
                } else {
                    $this->printSpace($depth);

                    if (is_string($key)) {
                        $printKey = '\'' . $this->quote($key) . '\'';
                        $keyClass = 'dumper-array-key-string';
                    } else {
                        $printKey = $this->quote($key);
                        $keyClass = 'dumper-array-key-int';
                    }

                    echo '<span class="' . $keyClass . '">' . $printKey . '</span>' . self::CHAR_SPACE;
                    $this->printEqual('=>');
                    echo self::CHAR_SPACE;
                    echo $this->dumpChild($val, $depth, $parentObjs);
                }
            }

            echo  $this->printSpace($depth - 1) . '</div>';
        }
    }

    /**
     * @param string $string
     * @param int $depth
     */
    private function printString($string, $depth)
    {
        $len = mb_strlen($string, mb_detect_encoding($string));

        if ($this->detailed) {
            $link   = false;
            $origin = null;

            if ($this->isLink($string)) {
                $origin = $string;
                $link   = true;
            }

            if ($len > 83) {
                $string = $this->quote(substr($string, 0, 50)) . '<span class="dumper-string-space">...</span>' .
                    $this->quote(substr($string, -30));
            } else {
                $string = $this->quote($string);
            }

            if ($link) {
                $string = '<a target="_blank" href="' . $origin . '">' . $string . '</a>';
            }
        } else {
            $string = $this->quote($string);
        }


        echo '<span class="dumper-type">string (<span class="dumper-count">';
        echo $len . '</span>)</span>';
        echo self::CHAR_SPACE . '<span class="dumper-string">\'' . $string . '\'</span><br />';
    }

    /**
     * @param $string
     * @return bool
     */
    private function isLink($string)
    {
        $link  = false;
        $parts = parse_url($string);

        if (isset($parts['scheme']) && isset($parts['host'])) {
            $link = true;
        }

        return $link;
    }

    /**
     * @param $text
     * @return string
     */
    private function quote($text)
    {
        return htmlspecialchars($text);
    }

    /**
     * @param int $depth
     */
    private function printSpace($depth)
    {
        echo '<span style="display:inline-block;padding-left:' . $depth * 20 . 'px"></span>';
    }

    /**
     * @param string $char
     */
    private function printEqual($char = '=')
    {
        echo '<span class="dumper-equal">' . self::CHAR_SPACE . $char . self::CHAR_SPACE . '</span>';
    }

    /**
     * @param $var
     * @return int|null
     */
    private function getType(&$var)
    {
        $result = null;

        if (is_callable($var)) {
            $result = self::TYPE_FUNCTION;
        } elseif (is_object($var)) {
            $result = self::TYPE_OBJECT;
        } elseif (is_array($var)) {
            $result = self::TYPE_ARRAY;
        } elseif (is_string($var)) {
            $result = self::TYPE_STRING;
        } elseif (is_numeric($var)) {
            $result = self::TYPE_NUMBER;
        } elseif (is_bool($var)) {
            $result = self::TYPE_BOOL;
        } elseif (is_resource($var)) {
            $result = self::TYPE_RESOURCE;
        } else {
            $result = self::TYPE_NULL;
        }

        return $result;
    }
}

