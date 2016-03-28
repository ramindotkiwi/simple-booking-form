<?php
/**
 * Created by PhpStorm.
 * User: etema
 * Date: 3/13/16
 * Time: 10:17 AM
 * A simple model for handling Booking Form and its features
 */
include_once $_SERVER['DOCUMENT_ROOT'] . "/components/database.php"; //Load database class

//Use IDatabase interface from database.php file
class bookingForm extends database implements IDatabase{

    /*
     * after __construct function this function call from database.php
     */
    public function init()
    {
        $sql = "CREATE TABLE IF NOT EXISTS booking
                (
                  id INT PRIMARY KEY AUTO_INCREMENT,
                  room_id INT ,
                  username VARCHAR (255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '' NOT NULL ,
                  start_time int,
                  end_time INT
                )
               ";

        $this->dbExec($sql);
    }
    /**
     * @return string
     */
    public function tableName()
    {
        return 'booking';
    }

    public function rules()
    {
        return array(
            'room_id'=>array('allowEmpty'=>false, ),
            'username'=>array('allowEmpty'=>true, 'maxLength'=>255),
            'start_time'=>array('allowEmpty'=>false),
            'end_time'=>array('allowEmpty'=>false)
        );
    }


    public function record($room_id, $username, $start_time, $end_time, &$reservedInfo = null)
    {
        //First find all rooms with room_id from booking table
        $criteria = array(
            'condition'=>'room_id=:i',
            'params'=>array(':i'=>array($room_id, PDO::PARAM_INT))
        );

        $results = $this->findAll($criteria);

        $reserved = false;
        $index = 0;
        if( is_array($results) ){
            foreach ($results as $k=>$r) {
                if( $start_time >= $r['start_time'] && $start_time <= $r['end_time'] ){
                    $reserved = true;
                    $index = $k;
                    break;
                }
                if( $end_time >= $r['start_time'] && $end_time <= $r['end_time']){
                    $reserved = true;
                    $index = $k;
                    break;
                }
            }

        }else $reserved = false;

        if( $reserved === true ) {
            $reservedInfo = array(
                'username'=>$results[$index]['username'],
                'start_time'=>date('Y/m/d H:i:s', $results[$index]['start_time']),
                'end_time'=>date('Y/m/d H:i:s', $results[$index]['end_time'])
            );
            return false;
        }

        return $this->save(null, array('room_id'=>$room_id, 'username'=>$username, 'start_time'=>$start_time, 'end_time'=>$end_time));
    }

    public function getRooms($q)
    {

        $sql = "SELECT * FROM rooms" . " WHERE title LIKE '%{$q}%' OR des LIKE '%{$q}%'";

        $sth = $this->dbQuery($sql);
        $sth->setFetchMode(PDO::FETCH_ASSOC);
        return $sth->fetchAll();
    }
}