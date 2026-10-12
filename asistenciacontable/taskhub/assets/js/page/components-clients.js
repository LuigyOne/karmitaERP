$('#fire-modal-31').on('hidden.bs.modal', function (e) {
    $("#email").val('');
    $("#first_name").show();
    $("#last_name").show();
    $("#password_confirm").show();
    $("#password").show();
    $("#address").show();
    $("#city").show();
    $("#state").show();
    $("#country").show();
    $("#zip_code").show();
});

function queryParams(p) {
    return {
        "user_type": 3,
        limit: p.limit,
        sort: p.sort,
        order: p.order,
        offset: p.offset,
        search: p.search
    };
}

