<div class="page-content">
    <div class="container-fluid">
        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">List Menu <?=ucwords($nama_menu)?></h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript:void(0);">
								<?php
									$result	=	$this->Master->get_row('pages',['menu_groupid'=>$menu_groupid,'is_child'=>0])->row();
									($nama_menu=='reports') ? $tabmenu = $nama_menu.'-'.$result->nama_menu : $tabmenu = $nama_menu;
									echo ucwords($result->nama_menu);
								?>
							</a></li>
                            <li class="breadcrumb-item active">Menu <?=ucwords($nama_menu)?></li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <!-- end page title -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
<?php
if($nama_menu != 'users' && $nama_menu != 'reports') {
?>
						<div class="row mb-2">
							<div class="col-sm-12">
								<div class="text-sm-end">
									<a href="#" type="button" class="btn btn-outline-success btn-rounded waves-effect waves-light mb-2 me-2 btn-sm btn-action" data-view="form" data-action="/api/client/<?=$nama_menu?>/<?=$url?>">
										<i class="mdi mdi-plus me-1"></i> Tambah Data
									</a>
								</div>
							</div>
						</div>
<?php
}
?>
                        <div class="table-responsive">
                            <table class="table align-middle table-nowrap table-check" id="tab-<?=$tabmenu?>" data-action="/api/client/<?=$nama_menu?>/table" style="width: 100%;" ><thead class="table-light"><tr></tr></thead></table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div> <!-- container-fluid -->
</div>
<!-- End Page-content -->
