var $table = $('#table')
var $remove = $('#remove')
var selections = []

function getIdSelections() {
    return $.map($table.bootstrapTable('getSelections'), function (row) {
        return row.id
    })
}

function responseHandler(res) {
    $.each(res.rows, function (i, row) {
        row.state = $.inArray(row.id, selections) !== -1
    })
    return res
}

function initTable() {
    $table.bootstrapTable('destroy').bootstrapTable({
        height: 600,
        locale: 'en-US',
        exportTypes: ['csv','excel', 'txt']
    })
    $table.on('check.bs.table uncheck.bs.table ' +
        'check-all.bs.table uncheck-all.bs.table',
        function () {
            $remove.prop('disabled', !$table.bootstrapTable('getSelections').length)

            // save your data, here just save the current page
            selections = getIdSelections()
            // push or splice the selections if you want to save all data selections
        })
    $table.on('all.bs.table', function (e, name, args) {
        console.log(name, args)
    })
    $remove.click(function () {
        var ids = getIdSelections()
        $table.bootstrapTable('remove', {
            field: 'id',
            values: ids
        })
        $remove.prop('disabled', true)
    })
}

$(function () {

    initTable()

    $('#toolbar').find('select').change(function () {
        // $table.bootstrapTable('destroy').bootstrapTable({
        //     exportDataType: $(this).val(),
        //     exportTypes: ['csv', 'txt', 'excel'],
        // })
    }).trigger('change')

})
$('#myModal').on('shown.bs.modal', function () {
    $('#myInput').trigger('focus')
})

$(document).ready(function () {
    $('[data-toggle="tooltip"]').tooltip({
        animation: true,
        html: true
    })
})