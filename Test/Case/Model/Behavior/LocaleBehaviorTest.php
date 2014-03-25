<?php
App::uses('LocaleBehavior', 'Locale.Model/Behavior');
App::uses('LocaleException', 'Locale.Lib');
/**
 * Behavior Locale Tests
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright 2009-2013, Radig - Soluções em TI, www.radig.com.br
 * @link http://www.radig.com.br
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 *
 * @package Plugin.Locale
 * @subpackage Plugin.Locale.Test.Case.Model.Behavior
 */

class Employee extends CakeTestModel
{
	public $name = 'Employee';

	public $validate = array(
		'birthday' => array(
			'rule' => array('date'),
			'allowEmpty' => false,
			'required' => true,
			'message' => 'invalid date'
		),
		'salary' => array(
			'rule' => array('numeric'),
			'allowEmpty' => false,
			'required' => true,
			'message' => 'invalid numeric'
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
			'allowEmpty' => true,
			'required' => false
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

class Event extends CakeTestModel
{
	public $name = 'Event';

	public $validate = array(
		'when' => array(
			'rule' => array('datetime'),
			'allowEmpty' => true,
			'required' => false
		),
		'title' => array(
			'rule' => array('minLength', 4),
			'allowEmpty' => false,
			'required' => true
		)
	);

	public $actsAs = array('Locale.Locale');
}

class LocaleBehaviorTest extends CakeTestCase {

	public $name = 'Locale';

	public $plugin = 'Locale';

	public $oldLocale = null;

	public $fixtures = array(
		'plugin.locale.employee',
		'plugin.locale.task',
		'plugin.locale.event'
	);

	public function setUp() {
		parent::setUp();

		$this->oldLocale = setlocale(LC_ALL, "0");
		setlocale(LC_ALL, 'pt_BR.utf-8', 'pt_BR', 'pt-br', 'portuguese');
	}

	public function tearDown() {
		parent::tearDown();

		setlocale(LC_ALL, $this->oldLocale);
		ClassRegistry::flush();
	}

	public function testFindActionWithDate() {
		$Employee = ClassRegistry::init('Employee');
		$result = $Employee->find('all',
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

	/**
	 * Behavior don't change invalid date values
	 *
	 * @return void
	 */
	public function testFindActionWithBogusDate() {
		$Employee = ClassRegistry::init('Employee');

		if (!is_a($Employee->getDataSource(), 'Mysql')) {
			$this->setExpectedException('PdoException');
		}

		$result = $Employee->find('all',
			array('conditions' => array('birthday' => '21/23/1987'))
		);

		$this->assertEquals($result, array());
	}

	public function testFindActionConditionString() {
		$Employee = ClassRegistry::init('Employee');
		$this->skipIf(!is_a($Employee->getDataSource(), 'Mysql'), 'Sintaxe da SQL válida apenas para Mysql');

		$result = $Employee->find('all',
			array('conditions' => array('birthday IS NULL'))
		);

		$expected = array();

		$this->assertEquals($result, $expected);
	}

	public function testFindActionWithFloat() {
		$Employee = ClassRegistry::init('Employee');
		$result = $Employee->find('all',
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

	public function testFindActionWithFloatWithoutDot() {
		$Employee = ClassRegistry::init('Employee');
		$result = $Employee->find('all',
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

	public function testSaveNonLocalizedDataAction() {
		$Employee = ClassRegistry::init('Employee');
		$result = $Employee->save(
			array('id' => '2', 'birthday' => '2001-01-01', 'salary' => '650.30')
		);

		$expected = array('Employee' => array(
			'id' => '2',
			'birthday' => '2001-01-01',
			'salary' => '650.30'
			)
		);

		$this->assertEquals($result, $expected);

		$result = $Employee->save(
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

	public function testSaveNullDate() {
		$Task = ClassRegistry::init('Task');

		$result = $Task->save(
			array('id' => 5, 'title' => 'bla bla', 'term' => null, 'employee_id' => 1)
		);

		$expected = array(
			'Task' => array(
				'id' => '5',
				'title' => 'bla bla',
				'term' => null,
				'employee_id' => 1
			)
		);

		$this->assertEquals($result, $expected);
	}

	public function testSaveLocalizedDataAction() {
		$Employee = ClassRegistry::init('Employee');
		$result = $Employee->save(
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

		$result = $Employee->save(
			array('id' => '20', 'birthday' => '01/01/2001', 'salary' => '1.650,30')
		);

		$this->assertTrue(is_array($result));
	}

	public function testSaveWrongDate() {
		$Employee = ClassRegistry::init('Employee');
		$result = $Employee->save(
			array('id' => '2', 'birthday' => '01-01-2001', 'salary' => '650.30')
		);

		$this->assertFalse($result);
		$this->assertEquals(array('birthday' => array('invalid date')), $Employee->validationErrors);

		// test with a invalid date (month = 14)
		$result = $Employee->save(
			array('id' => '2', 'birthday' => '2001/14/01', 'salary' => '650.30')
		);

		$this->assertFalse($result);
		$this->assertEquals(array('birthday' => array('invalid date')), $Employee->validationErrors);
	}

	public function testSaveAllAction() {
		$Employee = ClassRegistry::init('Employee');
		$result = $Employee->saveAll(
			array(
				array('id' => '2', 'birthday' => '01/01/2001', 'salary' => '650,30'),
				array('id' => '3', 'birthday' => '29/03/1920', 'salary' => '0,99'),
				array('id' => '4', 'birthday' => '21/04/1975', 'salary' => '0,3')
			)
		);
		$this->assertEquals($result, true);
	}

	public function testSavedData() {
		$Employee = ClassRegistry::init('Employee');
		$result = $Employee->saveAll(
			array(
				array('id' => '2', 'birthday' => '01/01/2001', 'salary' => '650,30'),
				array('id' => '3', 'birthday' => '29/03/1920', 'salary' => '0,99'),
				array('id' => '4', 'birthday' => '21/04/1975', 'salary' => '0,3'),
				array('id' => '5', 'birthday' => '28/02/2001', 'salary' => '123.456,78')
			)
		);

		$this->assertEquals($result, true);

		$floatSQLVal = '123456.78';
		if (is_a($Employee->getDataSource(), 'Mysql')) {
			$floatSQLVal = '123457';
		}

		$saved = $Employee->find('all', array('conditions' => array('id' => array(2,3,4,5))));
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
				'Employee' => array('id' => '5', 'birthday' => '2001-02-28', 'salary' => $floatSQLVal)
			)
		);

		$this->assertEquals($saved, $expected);
	}

	public function testModelRelation() {
		$Employee = ClassRegistry::init('Employee');
		$Task = ClassRegistry::init('Task');

		$employee = $Employee->find('first');

		$employee['Employee']['salary'] = '3640,30';

		$task = array(
			'Task' => array(
				'title' => 'Terminar o trabalho',
				'term' => '12/06/2012'
			),
			'Employee' => $employee['Employee']
		);

		$result = $Task->saveAll($task);

		$this->assertTrue($result);

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
	}

	public function testFindWithRecursiveConditions() {
		$Employee = ClassRegistry::init('Employee');
		$result = $Employee->find('all', array(
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
			array(
				'Employee' => array(
					'id' => 1,
					'birthday' => '1987-03-01',
					'salary' => '559'
					)
				),
			array(
				'Employee' => array(
					'id' => 2,
					'birthday' => '1987-09-01',
					'salary' => '699'
				)
			)
		);

		$this->assertEquals($expected, $result);
	}

	public function testSaveDateTime() {
		$Event = ClassRegistry::init('Event');
		$result = $Event->save(array('id' => 2, 'title' => 'My Event Title', 'when' => '09/06/2012 18:30'));

		$expected = array(
			'Event' => array(
				'id' => 2,
				'title' => 'My Event Title',
				'when' => '2012-06-09 18:30:00'
			)
		);

		$this->assertEquals($expected, $result);

		$Event->create();
		$result = $Event->save(array('id' => 3, 'title' => 'My Event Title', 'when' => '09/06/2012 18:30:43'));

		$expected = array(
			'Event' => array(
				'id' => 3,
				'title' => 'My Event Title',
				'when' => '2012-06-09 18:30:43'
			)
		);

		$this->assertEquals($expected, $result);
	}

	public function testFindDateTime() {
		$Event = ClassRegistry::init('Event');

		$Event->save(array('id' => 2, 'title' => 'My Event Title', 'when' => '09/06/2012 18:30'));
		$result = $Event->find('count', array('conditions' => array('when' => '09/06/2012 18:30')));
		$this->assertEquals(1, $result);

		$result = $Event->find('first', array('conditions' => array('when' => '09/06/2012 18:30')));

		$expected = array(
			'Event' => array(
				'id' => 2,
				'title' => 'My Event Title',
				'when' => '2012-06-09 18:30:00'
			)
		);

		$this->assertEquals($expected, $result);

		$result = $Event->find('first', array('conditions' => array('when' => '09/06/2012 18:30:00')));

		$this->assertEquals($expected, $result);
	}

	/**
	 * Behavior don't change invalid date values
	 *
	 * @return void
	 */
	public function testFindWithNullDate() {
		$Employee = ClassRegistry::init('Employee');

		if (!is_a($Employee->getDataSource(), 'Mysql')) {
			$this->setExpectedException('PdoException');
		}

		$result = $Employee->find('all', array(
			'conditions' => array(
				'birthday' => '0000-00-00'
			)
		));

		$expected = array();

		$this->assertEquals($expected, $result);
	}

	/**
	 * Behavior don't change invalid date values
	 *
	 * @return void
	 */
	public function testFindArrayOfDates() {
		$Employee = ClassRegistry::init('Employee');

		if (!is_a($Employee->getDataSource(), 'Mysql')) {
			$this->setExpectedException('PdoException');
		}

		$result = $Employee->find('all', array(
			'conditions' => array(
				'birthday' => array('0000-00-00', '1987-01-11')
			)
		));

		$expected = array();

		$this->assertEquals($expected, $result);
	}

	public function testFindArrayOfFloats() {
		$Employee = ClassRegistry::init('Employee');
		$result = $Employee->find('all', array(
			'conditions' => array(
				'salary' => array('665', '444')
			)
		));

		$expected = array();

		$this->assertEquals($expected, $result);
	}
}
