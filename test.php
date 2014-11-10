<?php
require __DIR__ . '/Dumper.php';
use Dumper\Dumper;

class TestClass extends TestClass2
{
    private    $test  = 'hallo';
    private    $test4 = 'hallo';
    protected $test2  = 'hallo';
    public    $test3  = 'hallo';

    /**
     * @param $hallo
     * @param $wurst
     */
    private function sieben($hallo, $wurst)
    {
    }

    public static function hallo($nothing)
    {
    }

    protected final function kalb($schnitzel)
    {
    }

    public static function vogel()
    {

    }
}

abstract class TestClass2
{
    private function wurst()
    {

    }

    public abstract static function vogel();
}

ini_set('display_errors', 'On');
error_reporting(E_ALL);



$temp = new stdClass();
$temp->hallo = 'test';
$temp->awfawf = null;
$temp->gsegesgsegseeg = 'test';
$temp->hasegs = 'test';
$temp->irgwas = function(){};
$temp->std  = fopen('php://stdout', 'r');
$temp->res  = fopen('test.php', 'r');
$temp->curl = curl_init('127.0.0.1');


//direct recursion
$b = array();
$a = array(
    'test2' => &$b
);

$a['test2']['test'] = &$b;

//indirect recursion
$ia = array();
$ib = array();
$ic = array();

$ib['ic'] = &$ic;
$ic['ia'] = &$ia;
$ia['ib'] = &$ib;

$temp->rekurs  = &$a;
$temp->rekurs2 = &$ia;
$temp->test    = $temp;
$temp->time    = new DateTime();

$test = new TestClass();

$data = array(
    'arrayTest' => $temp,
    'hallo'  => $test,
    'hallo2' => array(
        'test' => 15,
        'awf'  => 27.33588,
        'sgg'  => array(
            'test' => 'http://google.de',
            'awff' => 'http://pictures.ultranet.hotelreservation.com/images/cache/48/42/484235d7b975583ef645a43c9f6c1929.jpg?../../imagedata/UCHIds/42/4919342/result/300166_8_15735_359_600_76258_VAId248Seq2IMG83aa638b754626db0e1f5fed001dd7f3.jpg,,420,280,,,1,,,,,,,RW,0,0',
            'awfa' => $temp
        ),
        'awfawfa' => 'hallo2',
        'awfa' => $temp
    ),
    'hallo4' => array(
        'test' => 'hallo2',
        'awf'  => 'hallo2',
        'seg'  => array(
            'test' => 'hallo2',
            'awff' => true,
            'awfa' => $temp
        ),
        'awfawfa' => 'hallo2',
        'awfa' => $temp
    )
);










/**
 * The Dumper Call
 * ##########################################################################
 * ##########################################################################
 * ##########################################################################
 * ##########################################################################
 * ##########################################################################
 * ##########################################################################
 * ##########################################################################
 */
Dumper::dumpReflection(Dumper::getInstance(), true, true);



