<?php
class TaskFixture extends CakeTestFixture {
	public $name = 'Task';

	public $fields = array(
		'id' => array('type'=>'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
		'title' => array('type' => 'string', 'null' => false),
		'term' => array('type'=>'date', 'null' => true),
		'employee_id' => array('type' => 'integer', 'null' => false),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1))
	);

	public $records = array(
		array(
			'id'  => 100,
			'title' => 'The Mayan Prophecy',
			'term'  => '2012-12-21',
			'employee_id'  => 1
		)
	);
}