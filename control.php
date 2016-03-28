<?php
/**
 * Created by PhpStorm.
 * User: etema
 * Date: 3/13/16
 * Time: 11:01 AM
 */
error_reporting(E_ALL);

if( isset($_REQUEST['op']) ){

    include_once $_SERVER['DOCUMENT_ROOT'] . "/models/bookingForm.php";
    $bForm = new bookingForm();

    switch($_REQUEST['op']){
        case 'record':
            //normalize start_date
            $start_time = strtotime($_POST['start_time']);
            $end_time = $start_time + ($_POST['end_time']*60);

            $username = $_POST['username'];
            $room_id = $_POST['room_id'];

            if( empty($room_id) ) die( json_encode(array('error'=>'Please select a room.')));



            $r = $bForm->record($room_id, $username, $start_time, $end_time, $reservedInfo);

            if( $r !== true ){
                if( is_array($reservedInfo) ){
                    die( json_encode($reservedInfo) );
                }else{
                    die( json_encode(array('error'=>'unexpected error happened.')));
                }
            }else{
                die('{}');
            }
            break;
        case 'suggestion':
            $q = $_POST['q'];

            $r = $bForm->getRooms($q);
            die(json_encode($r));
            break;
    }
}
