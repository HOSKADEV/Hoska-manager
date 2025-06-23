// Call the dataTables jQuery plugin
$(document).ready(function () {
    $('#dataTable').DataTable({
        order: [[1, 'desc']],  // ترتيب حسب عمود العنوان أو أي عمود مناسب
        columnDefs: [{
            targets: 0,
            searchable: false,
            orderable: false,
            render: function (data, type, row, meta) {
                return meta.row + 1;
            }
        }]
    });
});
