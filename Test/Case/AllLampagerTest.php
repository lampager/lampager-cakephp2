<?php

App::uses('CakeTestSuite', 'TestSuite');

class AllLampagerTest extends CakeTestSuite
{
    public static function suite()
    {
        $suite = new CakeTestSuite('All Lampager test');
        $suite->addTestDirectoryRecursive(CakePlugin::path('Lampager') . 'Test' . DS . 'Case');
        return $suite;
    }
}
