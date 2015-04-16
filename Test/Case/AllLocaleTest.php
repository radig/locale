<?php
App::uses('CakeTestSuite', 'TestSuite');
class AllLocaleTestsTest extends CakeTestSuite
{
    public static function suite()
    {
        $suite = new CakeTestSuite('All Locale Tests');

        $suite->addTestDirectory(__DIR__ . '/Lib');
        $suite->addTestDirectory(__DIR__ . '/Model/Behavior');
        $suite->addTestDirectory(__DIR__ . '/View/Helper');

        return $suite;
    }
}
