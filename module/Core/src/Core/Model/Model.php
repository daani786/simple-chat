<?php
namespace Core\Model;

use \Zend\Db\TableGateway\TableGateway;
use \Zend\Db\Adapter\Adapter;
use \Zend\Db\Sql;
use \Zend\Db\Sql\Select;
use \Zend\Db\Sql\Where;

class Model extends TableGateway
{
	/** @var string Table name. */
	public $table;

	/** @var array Primary key column name. */
	protected $idColumn = 'id';


	/**
	 * Constructor
	 *
	 * @param Zend\Db\Adapter\Adapter $adapter
	 */
	public function __construct(
		Adapter $adapter//,
	) {
		parent::__construct($this->table, $adapter);
	}

	/**
	 * @param array $data.
	 * @return integer|null
	 */
	public function insert($data)
	{
		if( parent::insert($data)) {
			return (int) $this->getLastInsertValue();
		}
		return null;
	}

	/**
	 * @param array $where.
	 * @return array|null
	 */
	public function fetchOne($where = null)
	{
		$select = $this->sql->select();
		$select->limit(1);
		$rs = $this->select($where);
		if($rs && $rs->count()) {
			return $rs->current();
		}
		return null;
	}

	/**
	 * @param array $where.
	 * @return array
	 */
	public function fetchAll($where = null)
	{
		$rs = $this->select($where);
		return $rs->toArray();
	}

	/**
	 * @param integer $id.
	 * @return array|null
	 */
	public function fetchOneById($id)
	{
		return $this->fetchOne(array(
			$this->idColumn => $id
		));
	}

	/**
	 * @param int $id.
	 * @return int
	 */
	public function deleteById($id)
	{
		return $this->delete(array(
			$this->idColumn => $id
		));
	}

	/**
	 * @param string $field.
	 * @param string $value.
	 * @return boolean
	 */
	public function checkDuplicate($field, $value, $id = false, $idField = 'id')
	{
		//$select = new Select($this->table);
		$sql = $this->getSql();
		$select = $sql->select();
		//$select->columns(array('id','lower_email' => new \Zend\Db\Sql\Expression('LOWER(email)')));
		$select->where->addPredicate(new Sql\Predicate\Expression('LOWER('.$field.') = ?', strtolower($value)));
		if ($idField && $id) {
			$select->where->notequalTo($idField, $id);
		}
		$select->limit(1);
		$query = $sql->getSqlstringForSqlObject($select);
		//print_r($query);
		//die();
		$result = $this->selectWith($select);
		if($result && $result->count()) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @param array $where.
	 * @return array
	 */
	public function fetchForDataTable($columnsData, $params, $where)
	{
		$json = array();
		$sql = $this->getSql();
		$oSelect = new Select($this->table);
		//$oSelect = $sql->select();
		$oWhere = new Where();
		//select columns
		$columns = array();
		foreach ($columnsData as $key => $val) {
			if ($val['data'] && $val['useInDb']) {
				$columns[] = $val['data'];
			}
		}
		$oSelect->columns($columns);
		//$select->columns(array('id','name'));
		
		if ($where) {
			$oWhere = $oWhere->nest();
			$firstIteration = true;
			foreach ($where as $key => $val) {
				if(!$firstIteration) {
						$oWhere = $oWhere->like($key, "%".$val."%");
				} else {
					$oWhere = $oWhere->or->like($key, "%".$val."%");
					$firstIteration = false;
				}
			}
			$oWhere = $oWhere->unnest();
		}
		$oWhere->and;
		//where clause
		if (!empty($params['search'])) {
			$firstIteration = true;
			$oWhere = $oWhere->nest();
			foreach ($columnsData as $key => $val) {
				if ($val['data'] && $val['searchable'] && $val['useInDb']) {
					if(!$firstIteration) {
						$oWhere = $oWhere->or->like($val['data'], "%".$params['search']."%");
					} else {
						$oWhere = $oWhere->like($val['data'], "%".$params['search']."%");
						$firstIteration = false;
					}
				}
			}
			$oWhere = $oWhere->unnest();
		}

		$oSelect->where($oWhere);
		//create query for total records
		$select2 = clone $oSelect;
		$select2->columns(array('num_rows' => new \Zend\Db\Sql\Expression('COUNT(*)')));
		//echo $oSelect->__toString();
		//print_r($oSelect->assemble());
		//echo  $oSelect->getSqlString();
		//echo $oSelect->query();
		//$query2 = $this->getSql($oSelect);
		//print_r($query2);

		$json['qryForTotal'] = $sql->getSqlstringForSqlObject($oSelect);

		//get total records
		$result2 = $this->selectWith($select2);
		$result2 = $result2->toArray();
		$json['num_rows'] = $result2[0]['num_rows'];

		//add limit and order to select
		$oSelect->order(array($params['order_by'].' '.$params['order_dir']));
		$oSelect->limit($params['length']);
		$oSelect->offset($params['start']);

		//create query for currnet page records
		$query = $sql->getSqlstringForSqlObject($oSelect);
		$json['qry'] = $query;

		$result = $this->selectWith($oSelect);
		$json['result'] = $result->toArray();
		return $json;

		/*
		$statement2 = $sql->prepareStatementForSqlObject($select2);
		$results2 = $statement2->execute();
		print_r($results2->num_rows);
		die();
		*/
	}

}