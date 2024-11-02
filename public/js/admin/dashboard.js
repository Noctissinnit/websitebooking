$(document).ready(() => {
    $('.btn-edit-user').click(async function(){
        const data = await $.get(`${userGetUrl}?id=${$(this).attr('id')}`);
        
        const form = $('#form-edit-user');
        form.append(`<input id="form-user-method" type="hidden" name="_method" value="PUT">`);
        form.find('input[name="name"]').val(data.name);
        form.find('input[name="email"]').val(data.email);
        form.find('input[name="nis"]').val(data.nis);
        form.find('input[name="id"').val($(this).attr('id'));
        
        $('#userEditModal').modal('show');
    });
});