$(function() {
    let oImage = $("#image_android");
    let oInput = $("#android_code");
    let chooseFile = $("#chooseFile1");
    let rechooseFile = $("#rechooseFile1");
    let uploadFile = $("#uploadFile1");
    let deleteFile = $("#deleteFile1");
    var uploadImg =  $("#uploadImgAndroid");

    chooseFile.on("click", function() {
        uploadImg.click();
    });

    //绑定删除事件
    deleteFile.click(function(){
        chooseFile.show();
        let existObject = oInput.val();
        if (existObject) {
            $.ajax({
                type: 'POST',
                url: '/admin/user_upload/deleteFile',
                data: {
                    "object": existObject
                },
                dataType: 'json',
                success: function (data) {
                    if (data.code == 1) {
                        oInput.val('');
                        oImage.attr('src', '');
                        layer.msg('删除成功',{icon: 6});
                    } else {
                        layer.msg('上传失败',{icon: 6});
                    }
                },
                error: function (data) {
                    console.log(data.msg);
                },
            });
        }
    });

    uploadImg.fileupload({
        url : '/admin/user_upload/uploadFile',//请求发送的目标地址
        Type : 'POST',//请求方式 ，可以选择POST，PUT或者PATCH,默认POST
        formData: {
            "user_id":0,
        },
        //dataType : 'json',//服务器返回的数据类型
        autoUpload : false,
        acceptFileTypes : /(gif|jpe?g|png)$/i,//验证图片格式
        maxNumberOfFiles : 2,//最大上传文件数目
        maxFileSize : 2000000, // 文件上限2MB
        minFileSize : 100,//文件下限  100b
        messages : {
            acceptFileTypes : '文件类型不匹配',
            maxFileSize : '文件过大',
            minFileSize : '文件过小',
        },
        success:function (data) {
            rechooseFile.hide();
            let dataStr =$.parseJSON(data);
            console.log(dataStr);
            if(dataStr.code === 1 && dataStr.data.type ===1){
                $("#android_code").val(dataStr.data.save_dir);
            }
        },
    })
    //图片添加完成后触发的事件
    .on("fileuploadadd", function(e, data) {
        validate(data.files[0]); //这里也可以手动来验证文件格式和大小
        $(".img-preview").show();
        uploadFile.show();
        rechooseFile.show();
        chooseFile.hide();

        //获取图片路径并显示
        var url = getUrl(data.files[0]);
        oImage.attr("src", url);
        oImage.attr("width", '150px');
        oImage.attr("height", '150px');

        //绑定开始上传事件
        uploadFile.click(function() {
            uploadFile.hide();
            jqXHR = data.submit();
            //解绑，防止重复执行
            uploadFile.off("click");
        });

        //绑定点击重选事件
        rechooseFile.click(function(){
            $("#uploadImgAndroid").click();
            //解绑，防止重复执行
            rechooseFile.off("click");
        });

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
    //显示上传进度条
    .on("fileuploadprogressall", function(e, data) {
        let $progress = $('#progress');
        $progress.show();
        var progress = parseInt(data.loaded / data.total * 100, 10);
        $progress.css(
            'width','200px'
        );
        $('#progress .bar').css(
            'width',progress + '%'
        );
    })
    //上传请求失败时触发的回调函数
    .on("fileuploadfail", function(e, data) {
        layer.alert(data.errorThrown,{icon:7});
    })
    //上传请求成功时触发的回调函数
    .on("fileuploaddone", function(data) {
        layer.msg('上传成功',{icon: 5});
    })
    //上传请求结束后，不管成功，错误或者中止都会被触发
    .on("fileuploadalways", function(e, data) {

    });


    //手动验证
    function validate(file) {
        //获取文件名称
        var fileName = file.name;
        //验证图片格式
        if (!/.(gif|jpg|jpeg|png|gif|jpg|png)$/.test(fileName)) {
            layer.msg("文件格式不正确");
            return true;
        }
        //验证excell表格式
        /*  if(!/.(xls|xlsx)$/.test(fileName)){
             alert("文件格式不正确");
             return true;
         } */

        //获取文件大小
        var fileSize = file.size;
        if (fileSize > 1024 * 1024) {
            layer.msg("文件不得大于2m");
            return true;
        }
        return false;
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
