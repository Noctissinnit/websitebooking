$(document).ready(() => {
    $('#btn-add-user').click(function(){
        clearFormUser();

        const form = $('#form-user');
        form.find('#form-user-method').remove();
        form.find('option').removeAttr('selected');
        form.find('input[name="password"]').css('display', '').attr('required', '');

        form.attr('action', userStoreUrl);
    })
    $('.btn-edit-user').click(async function(){
        clearFormUser();

        const data = await $.get(`${userGetUrl}?id=${$(this).attr('id')}`);
        
        const form = $('#form-user');
        form.find('input[name="password"]').css('display', 'none').removeAttr('required');

        form.append(`<input id="form-user-method" type="hidden" name="_method" value="PUT">`);
        form.find('input[name="name"]').val(data.name);
        form.find('input[name="email"]').val(data.email);
        form.find('input[name="nis"]').val(data.nis);
        form.find('input[name="id"]').val($(this).attr('id'));

        form.find(`select[name="department_id"] option[value=${data.department_id}]`).attr('selected', '');
        form.find(`select[name="jabatan_id"] option[value=${data.jabatan_id}]`).attr('selected', '');

        form.attr('action', userUpdateUrl);
        
        $('#userModal').modal('show');
    });
});

function clearFormUser(){
    $('#form-user')[0].reset();
}