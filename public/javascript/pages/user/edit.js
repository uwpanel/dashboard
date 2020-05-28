function randomString() {
    var chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXTZabcdefghiklmnopqrstuvwxyz';
    var string_length = 10;
    var randomstring = '';
    for (var i = 0; i < string_length; i++) {
        var rnum = Math.floor(Math.random() * chars.length);
        randomstring += chars.substr(rnum, 1);
    }
    document.v_edit_user.v_password.value = randomstring;
}

$(document).ready(function () {
    $('.add-ns-button').click(function () {
        var n = $('input[name^=v_ns]').length;
        if (n < 8) {
            var t = $($('input[name=v_ns1]').parents('div')[0]).clone(true, true);
            t.find('input').attr({
                value: '',
                name: 'v_ns' + (n + 1),
                placeholder: 'Name Server ' + (n + 1),
            });
            t.find('span').show();
            $('div.add-ns').before(t);
        }
        if (n == 7) {
            $('.add-ns').hide();
        }
    });

    $('.remove-ns').click(function () {
        $(this).parents('div')[0].remove();
        $('input[name^=v_ns]').each(function (i, ns) {
            $(ns).attr({
                name: 'v_ns' + (i + 1),
                placeholder: 'Name Server ' + (i + 1),
            });
            i < 2 ? $(ns).parent().find('span').hide() : $(ns).parent().find('span').show();
        });
        $('.add-ns').show();
    });

    $('input[name^=v_ns]').each(function (i, ns) {
        i < 2 ? $(ns).parent().find('span').hide() : $(ns).parent().find('span').show();
    });
});