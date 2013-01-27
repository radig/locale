<?php
class EmployeeFixture extends CakeTestFixture {
	public $name = 'Employee';

	public $fields = array(
		'id' => array('type'=>'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
		'birthday' => array('type'=>'date', 'null' => false, 'default' => NULL),
		'salary' => array('type'=>'float', 'null' => false, 'default' => 0.00),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1))
	);

	public $records = array(
		array(
			'id'  => 1,
			'birthday'  => '1987-03-01',
			'salary'  => '559.00'
		),
		array(
			'id'  => 2,
			'birthday'  => '1987-09-01',
			'salary'  => '699.00'
		)
	);
}
