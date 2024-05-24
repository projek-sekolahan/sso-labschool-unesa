<?php
class FacesModels extends CI_Model {

    function loadFaces($param) {
        $users          = $this->Master->get_row('users_groups',['user_id'=>$param])->row();
        $sqlgetfaceid   = "SELECT a.facecam_id, b.level FROM  users_login a, token b
        WHERE a.active=1 AND a.id=b.user_id AND a.facecam_id is NOT NULL AND b.level=".$users->group_id;
        return $this->Master->get_custom_query($sqlgetfaceid)->result();
    }

}
?>