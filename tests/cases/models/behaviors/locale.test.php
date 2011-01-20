<?php
App::import('Behavior', 'Locale');

/**
 * Testes do Behavior Locale
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @filesource
 * @author        Cauan Cabral <cauan@radig.com.br>, José Agripino <jose@radig.com.br>
 * @license       http://www.opensource.org/licenses/mit-license.php The MIT License
 */

class Employee extends CakeTestModel
{
	public $name = 'Employee';
	
	public $validate = array(
		'birthday' => array(
			'rule' => array('date'),
			'allowEmpty' => false,
			'requirerd' => true
		),
		'salary' => array(
			'rule' => array('numeric'),
			'allowEmpty' => false,
			'requirerd' => true
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


class LocaleTest extends CakeTestCase {
	
	public $name = 'Locale';
	
	public $fixtures = array('plugin.locale.employee', 'plugin.locale.task');
	
	public function startTest()
	{
		$this->Employee =& ClassRegistry::init('Employee');
	}

	public function endTest()
	{
		unset($this->Employee);
	}
	
	/**
	 * Testa uma ação de busca, com o critério tendo
	 * um valor de data localizada
	 */
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
		
		$this->assertEqual($result, $expected);
	}

	/**
	 * Testa uma ação de busca, com o critério tendo
	 * um valor de data (inválida) localizada
	 */
	public function testFindActionWithBogusDate()
	{
		$result = $this->Employee->find('all',
			array('conditions' => array('birthday' => '21/23/1987'))
		);

		$expected = array();

		$this->assertEqual($result, $expected);
	}
	
	/**
	 * Testa uma ação de busca, com o critério tendo
	 * um valor decimal localizado
	 */
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
		
		$this->assertEqual($result, $expected);
	}
	
	/**
	 * Testa uma ação de busca, com o critério sendo um inteiro,
	 * enquanto o banco espera um valor decimal/float
	 */
	public function testFindActionWithFloatWithoutDot()
	{
		$result = $this->Employee->find('all',
			array('conditions' => array('salary' => '559'))
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

		$this->assertEqual($result, $expected);
	}
	
	/**
	 * Testa o behavio para a ação save, com dados não localizados
	 * (já no formato alvo - do DB)
	 */
	public function testSaveNonLocalizedDataAction()
	{
		$result = $this->Employee->save(
			array('id' => '2', 'birthday' => '2001-01-01', 'salary' => '650.30')
		);
		
		$this->assertEqual($result, true);
	}
	
	/**
	 * Testa o behavior para a ação save, com dados localizados
	 */
	public function testSaveLocalizedDataAction()
	{
		$result = $this->Employee->save(
			array('id' => '2', 'birthday' => '01-01-2001', 'salary' => '650,30')
		);
		
		$this->assertEqual($result, true);
	}
	
	/**
	 * Testa se o behavior converte todos os dados
	 * salvos em um saveAll
	 */
	public function testSaveAllAction()
	{
		$result = $this->Employee->saveAll(
			array(
				array('id' => '2', 'birthday' => '01/01/2001', 'salary' => '650,30'),
				array('id' => '3', 'birthday' => '29/03/1920', 'salary' => '0,99'),
				array('id' => '4', 'birthday' => '21-04-1975', 'salary' => '0,3')
			)
		);
		$this->assertEqual($result, true);
	}
	
	/**
	 * Testa se os dados enviados pelo usuário serão
	 * convertidos do formato local para um formato padrão (do DB)
	 * e depois recuperados neste formato do DB.
	 */
	public function testSavedData()
	{
		$result = $this->Employee->saveAll(
			array(
				array('id' => '2', 'birthday' => '01/01/2001', 'salary' => '650,30'),
				array('id' => '3', 'birthday' => '29/03/1920', 'salary' => '0,99'),
				array('id' => '4', 'birthday' => '21-04-1975', 'salary' => '0,3')
			)
		);
		
		$expected = array(
			array(
				'Employee' => array(
					'id' => 1,
					'birthday' => '1987-03-01',
					'salary' => 559.00
				)
			),
			array(
				'Employee' => array(
					'id' => 2,
					'birthday' => '2001-01-01',
					'salary' => 650.3
				)
			),
			array(
				'Employee' => array(
					'id' => 3,
					'birthday' => '1920-03-29',
					'salary' => 0.99
				)
			)
			,array(
				'Employee' => array(
					'id' => 4,
					'birthday' => '1975-04-21',
					'salary' => 0.3
				)
			)
		);
		
		$this->assertEqual($result, $expected);
	}

	public function testModelRelation()
	{
		$Task =& ClassRegistry::init('Task');

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

		$this->assertEqual($result, $expected);

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

		$this->assertEqual($expected, $result);

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
		
		$this->assertEqual($expected, $result);
	}
	
	public function testFindWithNullDate()
	{
		$result = $this->Employee->find('all', array(
			'conditions' => array(
				'birthday' => '0000-00-00'
			)
		));
		
		$expected = array();
		
		$this->assertEqual($expected, $result);
	}
	
	public function testFindArrayOfDates()
	{
		$result = $this->Employee->find('all', array(
			'conditions' => array(
				'birthday' => array('0000-00-00', '1987-01-11')
			)
		));
		
		$expected = array();
		
		$this->assertEqual($expected, $result);
	}
	
	public function testFindArrayOfFloats()
	{
		$result = $this->Employee->find('all', array(
			'conditions' => array(
				'salary' => array('665', '444')
			)
		));
		
		$expected = array();
		
		$this->assertEqual($expected, $result);
	}
}