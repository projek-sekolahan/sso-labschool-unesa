<?php
class FacesModels extends CI_Model {
    function loadFaces($param,$facecamID) {
        $users = $this->Master->get_row('users_groups',['user_id'=>$param])->row();
        if(empty($facecamID) || $facecamID == null) {
            $where = "AND b.level = ".$users->group_id;
        } else {
            $where = "AND b.user_id = ".$users->user_id;
        }
        $sqlgetfaceid   = "SELECT a.facecam_id, b.level FROM  users_login a, token b
        WHERE a.active=1 AND a.id=b.user_id AND a.facecam_id is NOT NULL ".$where;
        return $this->Master->get_custom_query($sqlgetfaceid)->result();
    }
}
?>