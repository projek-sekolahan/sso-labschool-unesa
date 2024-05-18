<!-- ========== Left Sidebar Start ========== -->
<div class="vertical-menu">

<div data-simplebar class="h-100">

    <!--- Sidemenu -->
    <div id="sidebar-menu">
        <!-- Left Menu Start -->
        <ul class="metismenu list-unstyled" id="side-menu">
            <li>
                <a href="#" data-action="overview" class="waves-effect">
                    <i class="bx bx-home-circle"></i>
                    <span key="t-dashboards">Dashboards</span>
                </a>
            </li>
<?php
foreach($content as $pages) {
	foreach($pages as $item) {
		if ($item->tipe_site == "1") {
			if ($item->is_child == "0" && $item->is_execute == "1") {
?>
			<li>
				<a href="#" data-action="#" class="has-arrow waves-effect">
					<i class="bx <?=$item->icon?>"></i>
					<span key="t-<?=$item->nama_menu?>">Menu <?=ucwords($item->nama_menu)?></span>
				</a>
				<ul class="sub-menu" aria-expanded="false">
<?php
				foreach($pages as $val) {
					if ($val->is_child == "1" && $val->is_execute == "1" && $val->menu_groupid == $item->menu_groupid) {
?>
					<li>
						<a href="#" data-action="<?=$val->title?>" class="waves-effect">
							<i class="bx <?=$val->icon?>"></i>
							<span key="t-<?=$val->nama_menu?>">Menu <?=ucwords($val->nama_menu)?></span>
						</a>
					</li>
<?php
					}
				}
?>
				</ul>
			</li>
<?php
			}
		}
	}
}
?>
        </ul>
    </div>
    <!-- Sidebar -->
</div>
</div>
<!-- Left Sidebar End -->
