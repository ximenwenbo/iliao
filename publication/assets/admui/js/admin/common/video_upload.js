$(function() {
   for(let i = 0; i < 9; i++){
       let chooseFile = $("#chooseFile_"+i);
       let file_upload =  $(".file_upload_"+i);
       let oldObject = $("#video_"+i).attr('value');
       chooseFile.on("click", i,function() {
           $(this).parent().children('input').eq(0).click();
       });
       file_upload.fileupload({
           url : '/admin/user_upload/uploadFile',//上传服务器地址
           fileName:"video",   //后台接收的参数
           Type : 'POST',//请求方式 ，可以选择POST，PUT或者PATCH,默认POST
           dataType : 'json',//服务器返回的数据类型
           formData: {
               "del_object": oldObject
           },
           sequential:true,  //按顺序上传
           sequentialCount:1,  //按顺序上传
           autoUpload : true, //是否自动上传  false 否  true 是
           acceptFileTypes : '@',//允许上传的格式 @ 所有
           maxNumberOfFiles : 1,//最大上传文件数目
           maxFileCount:10,       //上传文件最大数量
           success:function (data) {
               let video = $("#video_"+i);
               let tmp = '<video class="video plyr--setup" style="height: 240px;">\n' +
                   '                                            <source type="video/mp4" src="'+data.data.abs_path+'">\n' +
                   '                                        </video>';
               video.attr('value',data.data.save_dir);
               video.parent().parent().children().eq(0).children().find('video').remove();
               video.parent().parent().children().eq(0).children().eq(0).append(tmp);
               //console.log(a);
           }
       })
       //图片添加完成后触发的事件
       .on("fileuploadadd", function(e, data) {

       })
       //当一个单独的文件处理队列结束触发(验证文件格式和大小)
       .on("fileuploadprocessalways", function(e, data) {
           //获取文件
           let file = data.files[0];
           //获取错误信息
           if (file.error) {

           }
       })
       //上传请求失败时触发的回调函数
       .on("fileuploadfail", function(e, data) {
           layer.alert(data.errorThrown,{icon:7});
       })
       //上传请求成功时触发的回调函数
       .on("fileuploaddone", function(data) {
           layer.msg('上传成功',{icon: 6});
       })
       //上传请求结束后，不管成功，错误或者中止都会被触发
       .on("fileuploadalways", function(e, data) {

       });
   }

});
