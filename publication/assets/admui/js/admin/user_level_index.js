/**
 * 活动推广列表
 *
 */
(function(document, window, $) {
    'use strict';

    /* global moment, toastr */
    $(function() {
        var oTable;
        var $filterDate = $('#filter-date');
        var $filterForm = $('#table-form');

        // 表格初始化
        oTable = $('.dataTable').DataTable(
            $.concatCpt('dataTable', {
                autoWidth: false,
                processing: true,
                serverSide: true,
                searching: false,
                pagingType: 'simple_numbers',
                columns: [
                    {data: 'level_id'},
                    {data: 'level_name'},
                    {data: 'level_point'},
                    {data: 'create_time'},
                    {data: 'opera'},
                ],
                columnDefs: [
                    {"orderable":false,"aTargets":[1,2,3,4]}// 制定列不参与排序
                ],
                order:[
                    [0,'desc'],
                ],

                ajax: function(data, callback) {
                    $.ajax({
                        url: '/admin/user_level/IndexList',
                        cache: false,
                        dataType: 'JSON',
                        data: $.extend(
                            {data: $filterForm.serializeObject()},
                            {
                                pageIndex: data.start + 0,
                                sortField: data.order[0].column,
                                sortType: data.order[0].dir,
                                pageSize: data.length
                            }
                        ),
                        success: function(res) {
                            callback({
                                recordsTotal: res.total,
                                recordsFiltered: res.total,
                                data: res.pageList
                            });
                        },
                        error: function(err) {
                            toastr.error(err);
                        }
                    });
                }
            })
        );

        // 提交日志筛选表单
        $filterForm.on('submit', function() {
            // 重载表格数据
            oTable.ajax.reload();
            return false;
        });

    });

})(document, window, jQuery);


