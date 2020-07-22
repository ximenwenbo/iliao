$(function() {

    $("#chooseFile").on("click", function() {
        $("#uploadImg").click();
    });
    let  rechooseFile = $("#rechooseFile");
    let  uploadFile = $("#uploadFile");
    $('#uploadImg').fileupload({
        url : '/admin/app_version/uploadFile',//请求发送的目标地址
        Type : 'POST',//请求方式 ，可以选择POST，PUT或者PATCH,默认POST
        //dataType : 'json',//服务器返回的数据类型
        autoUpload : false,
        acceptFileTypes : '@',//验证图片格式
        maxNumberOfFiles : 1,//最大上传文件数目
        maxFileSize : 5000000000, // 文件上限50MB
        minFileSize : 100,//文件下限  100b
        messages : {
            acceptFileTypes : '文件类型不匹配',
            maxFileSize : '文件过大',
            minFileSize : '文件过小',
        },
        success:function (data) {
            let dataStr =$.parseJSON(data);
            if(dataStr.code === 1 && dataStr.data.type === 2){
                $("#sdk_url").val(dataStr.data.save_path);
                $("#preview").text('已上传: ' + dataStr.data.save_name);
            }
        }
    })
    //图片添加完成后触发的事件
        .on("fileuploadadd", function(e, data) {
            //validate(data.files[0])这里也可以手动来验证文件格式和大小

            $('#progress').hide();
            $("#chooseFile").hide();
            uploadFile.show();
            rechooseFile.show();

            //获取图片路径并显示
            var url = getUrl(data.files[0]);
            $("#preview").val(url);

            //绑定开始上传事件
            $('#uploadFile').click(function() {
                uploadFile.hide();
                jqXHR = data.submit();
                //解绑，防止重复执行
                uploadFile.off("click");
            });

            //绑定点击重选事件
            rechooseFile.click(function(){
                $("#uploadImg").click();
                //解绑，防止重复执行
                rechooseFile.off("click");
            })
        })
        //当一个单独的文件处理队列结束触发(验证文件格式和大小)
        .on("fileuploadprocessalways", function(e, data) {
            //获取文件
            let file = data.files[0];
            //获取错误信息
            if (file.error) {
                uploadFile.hide();
            }
        })
        //上传请求失败时触发的回调函数
        .on("fileuploadfail", function(e, data) {
            layer.alert(data.errorThrown,{icon:7});
        })
        //上传请求成功时触发的回调函数
        .on("fileuploaddone", function(data) {
            layer.msg('上传成功');
        })
        //上传请求结束后，不管成功，错误或者中止都会被触发
        .on("fileuploadalways", function(e, data) {

        });

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