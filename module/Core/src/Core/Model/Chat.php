<?php
namespace Core\Model;

use \Zend\Db\Sql\Select;

class Chat extends Model
{
    public $table = 'chat';
    public function countAll($user_id = null)
    {
        $select = new Select($this->table);
        $select->columns(array('nor' => new \Zend\Db\Sql\Expression('COUNT(*)')));
        $result = $this->selectWith($select)->toArray();
        return $result[0]['nor'];
    }
    public function getChildRec($user_id)
    {
        //SELECT DISTINCT(status), count(*) FROM `user_search_breakups` group by status
        $sql = $this->getSql();
        $select = $sql->select();
        //$select->columns(array('status','count'=> new \Zend\Db\Sql\Expression('COUNT(*)')));
        //$select->where(array('id' => $user_id));
        //$select->where->OR(array('parent_id' => $user_id));
        $select->where
               ->nest
                   ->equalTo('id', $user_id)
                   ->or
                   ->equalTo('parent_id', $user_id)
               ->unnest;
        $select->order(array('parent_id' => 'asc'));
          //,'count' => 'COUNT(*)'
        //$select->group('status');
        $json['qr'] = $sql->getSqlstringForSqlObject($select);
        //print_r($json['qr']);
        //die();
        $json['rd'] = $this->selectWith($select)->toArray();
        return $json['rd'];
    }
    public function getChatRecords($limit = 10)
    {
        $sql = $this->getSql();
        $select = $sql->select();
        $select->order(array('doc' => 'desc'));
        $select->limit($limit);
        //$json['qr'] = $sql->getSqlstringForSqlObject($select);
        $json['rd'] = $this->selectWith($select)->toArray();
        return $json['rd'];
    }
    public function addChatRecord($data)
    {
        $this->removeOldestRecord();
        //insert
        if ($id = $this->insert($data)) {
            return $id;
        }
        return false;

    }
    public function removeOldestRecord($limit = 10)
    {
        $totalRec = $this->countAll();
        if ($totalRec == $limit) {
            $sql = $this->getSql();
            $select = $sql->select();
            $select->columns(array('id'));
            $select->order(array('doc' => 'asc'));
            $select->limit(1);
            //$json['qr'] = $sql->getSqlstringForSqlObject($select);
            $result = $this->selectWith($select)->toArray();
            if ($result && count($result) > 0) {
                $this->delete(array('id'=>$result[0]['id']));
            }
        }
        return true;
    }
}