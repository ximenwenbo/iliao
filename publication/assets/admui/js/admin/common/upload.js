$(function() {
    for (let i = 0; i < 9; i++) {
        let oldObject = $("#file_upload_"+i).attr('value');
        $('#file_upload_'+i).fileupload({
            url: '/admin/user_upload/uploadFile',//上传服务器地址
            Type: 'POST',//请求方式 ，可以选择POST，PUT或者PATCH,默认POST
            dataType: 'json',//服务器返回的数据类型
            formData: {
                "del_object": oldObject
            },
            sequential: true,  //按顺序上传
            sequentialCount: 1,  //按顺序上传
            autoUpload: true, //是否自动上传  false 否  true 是
            acceptFileTypes: '@',//允许上传的格式 @ 所有
            maxNumberOfFiles: 1,//最大上传文件数目
            maxFileCount: 10,       //上传文件最大数量
            success: function (data) {
                $('#file_upload_'+i).attr('title', data.data.save_name);
                $('#file_upload_'+i).attr('value', data.data.save_dir);
            }
        })
        //图片添加完成后触发的事件
        .on("fileuploadadd", function (e, data) {

        })
        //当一个单独的文件处理队列结束触发(验证文件格式和大小)
        .on("fileuploadprocessalways", function (e, data) {
            var length = data.files.length;
            for (var i = 0; i < length; i++) {
                if (!data.files[i].type.match(/^image\/(gif|jpeg|png|svg\+xml)$/)) {
                    data.files[i].filetype = 'other-file';
                } else {
                    data.files[i].filetype = 'image';
                }
            }
            var url = getUrl(data.files[0]);   //获取文件预览地址 window生成
            var preview = $(this).parent().children('.dropify-preview');//当前操作对象父级下的预览盒子
            var render_img = '<img src="' + url + '" width="100%" alt =""/>';//展示图片的容器
            var render_text = '<i class="dropify-font-file"></i><span class="dropify-extension">' + data.files[0].name + '</span>';
            //为了防止拖放和单击生成多个图片 先清除图片容器
            preview.children('.dropify-render').empty();
            if (data.files[0].filetype === 'image') {
                //添加图片容器
                preview.children('.dropify-render').append(render_img);
            } else {
                //图文展示
                preview.children('.dropify-render').append(render_text);
            }
            preview.show();
            //移除按钮显示
            $(this).next().css('display', 'block');
            preview.find('.dropify-filename-inner').html(data.files[0].name);
        })
        //上传请求失败时触发的回调函数
        .on("fileuploadfail", function (e, data) {
            layer.alert(data.errorThrown, {icon: 7});
        })
        //上传请求成功时触发的回调函数
        .on("fileuploaddone", function (data) {
            layer.msg('上传成功');
        })
        //上传请求结束后，不管成功，错误或者中止都会被触发
        .on("fileuploadalways", function (e, data) {

        });
    }
    //获取图片地址
    function getUrl(file) {
        var url = null;
        if (window.createObjectURL != undefined) {
            url = window.createObjectURL(file);
        } else if (window.URL != undefined) {
            url = window.URL.createObjectURL(file);
        } else if (window.webkitURL != undefined) {
            url = window.webkitURL.createObjectURL(file);
        }
        return url;
    }

});