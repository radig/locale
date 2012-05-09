<?php
class EventFixture extends CakeTestFixture {
	public $name = 'Event';

	public $fields = array(
		'id' => array('type'=>'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
		'title' => array('type' => 'string', 'null' => false),
		'when' => array('type'=>'datetime', 'null' => true),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1))
	);

	public $records = array(
		array(
			'id'  => 1,
			'title' => 'The Mayan Prophecy',
			'when'  => '2012-12-21 12:43:00',
		)
	);
}