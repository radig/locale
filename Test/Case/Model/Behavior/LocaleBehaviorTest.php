<?php
App::uses('LocaleBehavior', 'Locale.Model/Behavior');
App::uses('LocaleException', 'Locale.Lib');
/**
 * Behavior Locale Tests
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright 2009-2012, Radig - Soluções em TI, www.radig.com.br
 * @link http://www.radig.com.br
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 *
 * @package Radig.Locale
 * @subpackage Radig.Locale.Test.Case.Model.Behavior
 */
class Employee extends CakeTestModel
{
	public $name = 'Employee';

	public $validate = array(
		'birthday' => array(
			'rule' => array('date'),
			'allowEmpty' => false,
			'required' => true
		),
		'salary' => array(
			'rule' => array('numeric'),
			'allowEmpty' => false,
			'required' => true
		)
	);

	public $actsAs = array('Locale.Locale');
}

class Task extends CakeTestModel
{
	public $name = 'Task';

	public $validate = array(
		'term' => array(
			'rule' => array('date'),
			'allowEmpty' => false,
			'required' => true
		),
		'title' => array(
			'rule' => array('minLength', 4),
			'allowEmpty' => false,
			'required' => true
		)
	);

	public $belongsTo = array('Employee');

	public $actsAs = array('Locale.Locale');
}


class LocaleBehaviorTest extends CakeTestCase {

	public $name = 'Locale';

	public $fixtures = array('plugin.locale.employee', 'plugin.locale.task');

	public function setUp()
	{
		parent::setUp();

		$this->Employee = new Employee();
	}

	public function testFindActionWithDate()
	{
		$result = $this->Employee->find('all',
			array('conditions' => array('birthday' => '01/03/1987'))
		);

		$expected = array(
			array(
				'Employee' => array(
					'id' => 1,
					'birthday' => '1987-03-01',
					'salary' => 559.00
				)
			)
		);

		$this->assertEquals($result, $expected);
	}

	public function testFindActionWithBogusDate()
	{
		$result = $this->Employee->find('all',
			array('conditions' => array('birthday' => '21/23/1987'))
		);

		$expected = array();

		$this->assertEquals($result, $expected);
	}

	public function testFindActionWithFloat()
	{
		$result = $this->Employee->find('all',
			array('conditions' => array('salary' => '559.00'))
		);

		$expected = array(
			array(
				'Employee' => array(
					'id' => '1',
					'birthday' => '1987-03-01',
					'salary' => 559.00
				)
			)
		);

		$this->assertEquals($result, $expected);
	}

	public function testFindActionWithFloatWithoutDot()
	{
		$result = $this->Employee->find('all',
			array('conditions' => array('salary' => '559.00'))
		);

		$expected = array(
			array(
				'Employee' => array(
					'id' => '1',
					'birthday' => '1987-03-01',
					'salary' => 559
				)
			)
		);

		$this->assertEquals($result, $expected);
	}

	public function testSaveNonLocalizedDataAction()
	{
		$result = $this->Employee->save(
			array('id' => '2', 'birthday' => '2001-01-01', 'salary' => '650.30')
		);

		$expected = array('Employee' => array(
			'id' => '2',
			'birthday' => '2001-01-01',
			'salary' => '650.30'
			)
		);

		$this->assertEquals($result, $expected);

		$result = $this->Employee->save(
			array('id' => '3', 'birthday' => '2001-01-01', 'salary' => 50.00)
		);

		$expected = array('Employee' => array(
			'id' => '3',
			'birthday' => '2001-01-01',
			'salary' => '50.00'
			)
		);

		$this->assertEquals($result, $expected);
	}

	public function testSaveLocalizedDataAction()
	{
		$result = $this->Employee->save(
			array('id' => '2', 'birthday' => '01/01/2001', 'salary' => '650,30')
		);

		$expected = array(
			'Employee' => array(
				'id' => '2',
				'birthday' => '2001-01-01',
				'salary' => '650.30'
			)
		);

		$this->assertEquals($result, $expected);

		$result = $this->Employee->save(
			array('id' => '20', 'birthday' => '01/01/2001', 'salary' => '1.650,30')
		);

		$this->assertTrue(is_array($result));
	}

	public function testSaveWrongDate()
	{
		try{
			$result = $this->Employee->save(
				array('id' => '2', 'birthday' => '01-01-2001', 'salary' => '650.30')
			);

			$this->assertFalse($result);
		}
		catch(LocaleException $e)
		{
			$this->assertEqual($e->getMessage(), 'Data inválida para localização');
		}
	}

	public function testSaveAllAction()
	{
		$result = $this->Employee->saveAll(
			array(
				array('id' => '2', 'birthday' => '01/01/2001', 'salary' => '650,30'),
				array('id' => '3', 'birthday' => '29/03/1920', 'salary' => '0,99'),
				array('id' => '4', 'birthday' => '21/04/1975', 'salary' => '0,3')
			)
		);
		$this->assertEquals($result, true);
	}

	public function testSavedData()
	{
		$result = $this->Employee->saveAll(
			array(
				array('id' => '2', 'birthday' => '01/01/2001', 'salary' => '650,30'),
				array('id' => '3', 'birthday' => '29/03/1920', 'salary' => '0,99'),
				array('id' => '4', 'birthday' => '21/04/1975', 'salary' => '0,3'),
				array('id' => '5', 'birthday' => '28/02/2001', 'salary' => '123.456,78')
			)
		);

		$this->assertEquals($result, true);

		$saved = $this->Employee->find('all', array('conditions' => array('id' => array(2,3,4,5))));
		$expected = array(
			array(
				'Employee' => array('id' => '2', 'birthday' => '2001-01-01', 'salary' => '650.3')
			),
			array(
				'Employee' => array('id' => '3', 'birthday' => '1920-03-29', 'salary' => '0.99'),
			),
			array(
				'Employee' => array('id' => '4', 'birthday' => '1975-04-21', 'salary' => '0.3'),
			),
			array(
				'Employee' => array('id' => '5', 'birthday' => '2001-02-28', 'salary' => '123457')
			)
		);

		$this->assertEquals($saved, $expected);
	}

	public function testModelRelation()
	{
		$Task = new Task();

		$employee = $this->Employee->find('first');

		$employee['Employee']['salary'] = '3640,30';

		$task = array(
			'Task' => array(
				'title' => 'Terminar o trabalho',
				'term' => '12/06/2012'
			),
			'Employee' => $employee['Employee']
		);

		$result = $Task->saveAll($task);

		$expected = true;

		$this->assertEquals($result, $expected);

		$result = $Task->find('all');

		$expected = array(
			array(
				'Task' => array(
					'id' => 100,
					'title' => 'The Mayan Prophecy',
					'term' => '2012-12-21',
					'employee_id' => 1
				),
				'Employee' => array(
					'id' => 1,
					'birthday' => '1987-03-01',
					'salary' => 3640.3
				)
			),
			array(
				'Task' => array(
					'id' => 101,
					'title' => 'Terminar o trabalho',
					'term' => '2012-06-12',
					'employee_id' => 1
				),
				'Employee' => array(
					'id' => 1,
					'birthday' => '1987-03-01',
					'salary' => 3640.3
				)
			),
		);

		$this->assertEquals($expected, $result);

		unset($Task);
	}

	public function testFindWithRecursiveConditions()
	{
		$result = $this->Employee->find('all', array(
			'conditions' => array(
				'or' => array(
					'and' => array(
						'Employee.birthday >= ' => '01/01/1987',
						'Employee.salary >' => '600'
						),
					array('birthday <= ' => '01/08/1987')
				)
			)
		));

		$expected = array(
			0 => array(
				'Employee' => array(
					'id' => 2,
					'birthday' => '1987-09-01',
					'salary' => '699'
				)
			)
		);

		$this->assertEquals($expected, $result);
	}

	public function testFindWithNullDate()
	{
		$result = $this->Employee->find('all', array(
			'conditions' => array(
				'birthday' => '0000-00-00'
			)
		));

		$expected = array();

		$this->assertEquals($expected, $result);
	}

	public function testFindArrayOfDates()
	{
		$result = $this->Employee->find('all', array(
			'conditions' => array(
				'birthday' => array('0000-00-00', '1987-01-11')
			)
		));

		$expected = array();

		$this->assertEquals($expected, $result);
	}

	public function testFindArrayOfFloats()
	{
		$result = $this->Employee->find('all', array(
			'conditions' => array(
				'salary' => array('665', '444')
			)
		));

		$expected = array();

		$this->assertEquals($expected, $result);
	}
}