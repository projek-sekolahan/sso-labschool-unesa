<div class="row">
    <div class="col-12">
        <form
            class="form-horizontal custom-validation needs-validation"
            action="/api/client/roles/create_update"
            id="form-createRoles"
            novalidate="novalidate"
            method="post"
            accept-charset="utf-8"
            enctype="multipart/form-data">
			<div class="card">
                <div class="card-body">
                    <h4 class="card-title">Informasi Menu Roles</h4>
					<div class="row">
						<div class="col-6">
							<div class="mb-3">
								<label for="roles_users" class="form-label">Roles Users</label>
								<input name="param" id="param" type="hidden" class="form-control" readonly="readonly">
								<select name="roles_users" id="roles_users" class="form-control select2" required="required">
									<option value="">Pilih Roles Users</option>
								</select>
							</div>
						</div>
						<div class="col-6">
							<div class="mb-3">
								<label for="permissions_pages" class="form-label">Permissions Pages</label>
								<select name="permissions_pages[]" id="permissions_pages" required="required" class="select2 form-control select2-multiple" multiple="multiple">
									<option value="">Pilih Permissions Pages</option>
								</select>
							</div>
						</div>
					</div>
				</div>
				<div class="card-footer">
					<div class="d-flex flex-wrap gap-2">
						<button
							type="button"
							class="btn btn-primary waves-effect waves-light btn-simpan">Simpan</button>
					</div>
				</div>
			</div>
        </form>
    </div>
</div>
