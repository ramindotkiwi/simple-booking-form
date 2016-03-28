<?php
/*PDO connection and select, Insert, Update database Class and Controller*/

interface IDatabase{
    public function tableName();
    public function rules();
}


include_once $_SERVER['DOCUMENT_ROOT'] . "/components/Form.php";

//connect to database with pdo
class database extends Form{

	protected $db;
	/*
		database construct
	*/
	public function __construct(){

        $this->db = new PDO('mysql:host=localhost;dbname=booking;charset=utf8;', 'root', 'LRS');
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION || PDO::ERRMODE_WARNING);
        $this->db->exec('SET NAMES utf8');

		//run init first from child
		if( method_exists($this, 'init') ) $this->init();
	}

    /*
     * Exec function
     */
    public function dbExec($s){
        return $this->db->exec($s);
    }//end function

    /*
     * Query function
     */
    public function dbQuery($q){
        return $this->db->query($q);
    }//end function


	/*
		find all from database
	*/
	public function findAll($criteria = NULL, $order = NULL, $or_and = 'OR', $select = '*' ){

        if( method_exists($this, 'tableName') ) {
            $tn = $this->tableName();
            if( empty($tn) ) return false;
        }else return false;

		$sql = "SELECT " . $select . " FROM " . $this->tableName() . ' ';
		if( $criteria !== NULL ){
			$sql .= 'WHERE ';
	
			$condition = explode(',', $criteria['condition']);
			foreach($condition as $key=>$val){
				if( Empty($val) ) continue;
				$val = explode('=', $val);
				if( $criteria['params'][$val[1]][1] == PDO::PARAM_STR )
					$sql .= $val[0] . '=' . "'" . $criteria['params'][$val[1]][0] . "'";
				else 
					$sql .= $val[0] . '=' . $criteria['params'][$val[1]][0];
				
				$sql .= ' ';
				if( $key < count($condition)-1 ) $sql .= $or_and.' ';
			}
		}
		
		if( $order !== NULL ){
			$sql .= 'ORDER BY ' . $order['order'] . ' ';
			if( isset($order['sort']) ) $sql .= $order['sort'];
		}

		$STH = $this->db->query($sql);
		$STH->setFetchMode(PDO::FETCH_ASSOC);
		$list = array();
		while( $row = $STH->fetch() ):
			$list[] = $row;
		endwhile;
		
		return (count($list)-1 < 0) ? NULL : $list; 
	}
	
	/*
		find first record from database
	*/
	public function find($criteria = NULL, $order = NULL, $or_and = 'OR', $select = '*'){
		$list = $this->findAll($criteria, $order, $or_and, $select);
		return ($list !== NULL) ? $list[0] : NULL;
	}
	
	/*
		insert or update in database
	*/
	public function save($where = NULL, $param = NULL, $duplicate = false){

		if( $param == NULL ){
			$param = array();
			foreach($this->rules() as $key=>$val){
				$param[$key] = $this->{$key}; 
			}
		}

		if( $this->validate($param) ){

			if( $where !== NULL ){
				$sql = "UPDATE ".$this->tableName()." SET ";
				foreach($param as $key=>$val){
					$sql .= $key . "=?, ";
				}
				$sql = substr($sql, 0, strlen($sql)-2) . ' ';
				$sql .= 'WHERE ';
				foreach($where as $key=>$val){
					$sql .= $key . "=? AND ";
				}
				$sql = substr($sql, 0, strlen($sql)-strlen('AND ')) . ' ';
				$p = array_values($param);
				$STH = $this->db->prepare($sql);
				foreach($where as $key=>$val)
					$p[] = $val;

                $this->db->beginTransaction();

				$STH->execute($p);

                $this->db->rollBack();
				return true;
			}else{
				$sql = "INSERT INTO " . $this->tableName() . " (";
				foreach($param as $key=>$val){
					$sql .= $key . ", ";
				}
				$sql = substr($sql, 0, strlen($sql)-2);
				$sql .= ') VALUES (';
				for($i=0; $i<=count($param)-1; $i++){
					$sql .= ($i<count($param)-1) ? '?,' : '?';
				}
				$sql .= ')';
				if( $duplicate === true ){
					$sql .= ' ON DUPLICATE KEY UPDATE ';
					foreach($param as $key=>$val){
						if( is_string($val) )
							$sql .= $key . "='" . $val . "'";
						else 
							$sql .= $key . '=' .$val;
						$sql .= ', '; 
					}
					//remove ,
					$sql = substr($sql, 0, strlen($sql)-2);
				}
				$STH = $this->db->prepare($sql);
				$p = array_values($param);

//                $this->db->beginTransaction();

				$STH->execute($p);

//                $this->db->rollBack();
				return true;
			}
		}else return false;
	}
	
	/*
		delete record
	*/
	public function delete($where = NULL, $or_and = "AND"){

		if( $where != NULL ){
			$sql = "DELETE FROM ".$this->tableName() . " WHERE ";
			foreach($where as $key=>$val){
				$sql .= $key . "=? " . $or_and . " ";
			}
			$sql = substr($sql, 0, strlen($sql)-strlen($or_and.' ')) . ' ';
			foreach($where as $key=>$val)
				$p[] = $val;
			$STH = $this->db->prepare($sql);
			$STH->execute($p);
			return true;
		}else{
			return $this->deleteAll();
		}
	}

}