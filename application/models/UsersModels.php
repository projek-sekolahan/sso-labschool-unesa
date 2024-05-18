<?php
class UsersModels extends CI_Model {
	function getDetail($param) {
		$sqluser    = "SELECT a.*,b.group_id,c.description,d.facecam_id 
		from users_details a, users_groups b,groups c,users_login d 
		WHERE a.user_id=d.id and c.id=b.group_id AND a.user_id=b.user_id 
		AND (a.email='".$param."' or a.user_id='".$param."')";
		$result     = $this->Master->get_custom_query($sqluser)->row();
		if ($result==null) {
			return false;
		} else {
			$resultimg	= $this->Master->get_row('users_img',['user_id'=>$result->user_id])->row();
			$rltsosmed  = $this->Master->get_row('users_sosmed',['user_id'=>$result->user_id])->row();
			return array_merge($resultimg ? get_object_vars($resultimg) : [] , $rltsosmed ? get_object_vars($rltsosmed) : [] , $result ? get_object_vars($result) : []);
		}
	}
}
?>
