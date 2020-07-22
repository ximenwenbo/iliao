$(function() {
    var user_id = $("input[name='user_id']").val();
    //获取文件上传总数
    let fileUpload = $('.jquery-fileupload').children('div').length;
    let oInput = $("#uploadImg_0");
    let oImage = $("#image_0");
    let deleteFile = $("#deleteFile");

    //每个按钮对应的事件
    for (let i = 0; i < fileUpload; i++){
        let file_index = i;//当前索引
        let chooseFile = $("#chooseFile_" + file_index); //选择按钮
        let reselectionFile = $("#reselectionFile_" + file_index);//重选按钮
        let uploadFile = $("#uploadFile_" + file_index); //上传按钮
        let saveUpload = $("#saveUpload_" + file_index); //保存按钮
        let uploadImg = $("#uploadImg_" + file_index); //input file
        let progress = $("#progress_" + file_index);//灰色进度条
        let progress_bar = progress.children('.bar');//绿色进度条
        chooseFile.on("click", function() {
            uploadImg.click();
        });

        //上传事件-配置
        uploadImg.fileupload({
            url : '/admin/user_upload/uploadFile',//请求发送的目标地址
            Type : 'POST',//请求方式 ，可以选择POST，PUT或者PATCH,默认POST
            //dataType : 'json',//服务器返回的数据类型
            formData: {user_id:user_id},
            autoUpload : false,
            acceptFileTypes : /(gif|jpe?g|png)$/i,//验证图片格式
            maxNumberOfFiles : 1,//最大上传文件数目
            maxFileSize : 2000000, // 文件上限1MB
            minFileSize : 100,//文件下限  100b
            messages : {
                acceptFileTypes : '文件类型不匹配',
                maxFileSize : '文件过大',
                minFileSize : '文件过小',
            },
            success:function (data) {//上传完成后回调
                //console.log(data);
                //回调参数转对象
                let dataStr =$.parseJSON(data);
                //上传成功后赋值
                if(dataStr.code === 1 && dataStr.data.type ===1){
                    $("#save_path_"+file_index).val(dataStr.data.save_dir);
                }
            }
        })
        //图片添加完成后触发的事件
        .on("fileuploadadd",function(e, data) {
                validate(data.files[0]); //这里也可以手动来验证文件格式和大小
                oImage.show();
                //隐藏或显示页面元素
                chooseFile.hide();//选择按钮隐藏
                uploadFile.show();//上传按钮显示
                reselectionFile.show();//重选按钮显示

                //获取图片路径并显示
                let url = getUrl(data.files[0]);
                $("#image_"+file_index).attr("src", url);
                //绑定开始上传事件
                uploadFile.click(function(){
                    saveUpload.show();//显示保存按钮
                    chooseFile.hide();
                    uploadFile.hide();
                    jqXHR = data.submit();
                    //解绑，防止重复执行
                    uploadFile.off("click");
                });

                //绑定点击重选事件
                reselectionFile.click(function(){
                    $("#uploadImg_" + file_index).click();
                    saveUpload.hide();
                    chooseFile.hide();
                    uploadFile.show();
                    progress.hide();
                    progress_bar.show();
                    progress.css(
                        'width', '0%'
                    );
                    progress_bar.css(
                        'width', '0%'
                    );
                    progress_bar.text('');
                    //解绑，防止重复执行
                    reselectionFile.off("click");
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
        //显示上传进度条
        .on("fileuploadprogressall", function(e, data) {
            progress.show();
            let progress_width = parseInt(data.loaded / data.total * 100, 10);
            progress.css(
                'width','100%'
            );
            progress_bar.text(progress_width + '%');
            progress_bar.css(
                'width',progress_width + '%'
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
            //console.log(data);
        });
    }


    //绑定删除事件
    deleteFile.click(function(){
        var existObject = $("#uploadImg_0").attr('value');
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
                        $("#chooseFile_0").show();
                        layer.msg('删除成功',{icon: 6});
                    } else {
                        layer.msg('上传失败',{icon: 6});
                    }
                },
                error: function (data) {
                    console.log(data.msg);
                },
            });
        }else {
            layer.msg('文件地址为空');
        }
    });

    //手动验证
    function validate(file) {
        //获取文件名称
        let fileName = file.name;
        //验证图片格式
        if (!/.(gif|jpg|jpeg|png|gif|jpg|png)$/.test(fileName)) {
            console.log("文件格式不正确");
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
            alert("文件不得大于一兆");
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


    //时间插件
    $("input[name='date1']").daterangepicker(
        {
            singleDatePicker: true,//设置为单个的datepicker，而不是有区间的datepicker 默认false
            showDropdowns: true,//当设置值为true的时候，允许年份和月份通过下拉框的形式选择 默认false
            autoUpdateInput: false,//1.当设置为false的时候,不给与默认值(当前时间)2.选择时间时,失去鼠标焦点,不会给与默认值 默认true
            timePicker24Hour : true,//设置小时为24小时制 默认false
            timePickerSeconds: true, //时间显示到秒
            timePicker : true,//可选中时分 默认false
            locale: {
                format: "YYYY-MM-DD HH:mm",
                separator: " - ",
                daysOfWeek: ["日","一","二","三","四","五","六"],
                monthNames: ["一月","二月","三月","四月","五月","六月","七月","八月","九月","十月","十一月","十二月"]
            }

        }
    ).on('cancel.daterangepicker', function(ev, picker) {
        $("#date1").val("请选择日期");
        $("#submitDate").val("");
    }).on('apply.daterangepicker', function(ev, picker) {
        $("#submitDate").val(picker.startDate.format('YYYY-MM-DD HH:mm'));
        $("#date1").val(picker.startDate.format('YYYY-MM-DD HH:mm'));
    });

});
