<?php

require_once __DIR__ . '/../vendor/autoload.php';

$config = array(
    'accessKey' => 'eqk2ITAKUc67_s6RMKoUJgPZgijc_',
    'secretKey' => 'vvc67_s6RMKoUJgUc67_sRMR',
    'bucketName' => 'test',
    'baseUrl' => 'http://oxxx32vf1.bkt.clouddn.com/',
    'separator' => '!',
);
$qiniu = new \PFinal\Storage\Qiniu($config);

$baseUrl = $qiniu->url('');

$clientToken = $qiniu->getClientToken();

?>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>demo</title>
</head>
<body>

<h2>普通表单上传</h2>
<form method="post" action="http://upload.qiniu.com/" enctype="multipart/form-data">
    <input name="token" type="hidden" value="<?= $clientToken['token'] ?>">
    <input name="file" type="file"/>

    <input type="submit">
</form>

<hr>

<h2>jquery-file-upload</h2>
<link rel="stylesheet" href="https://cdn.bootcss.com/bootstrap/3.3.7/css/bootstrap.min.css">
<link rel="stylesheet" href="./jquery-file-upload/css/jquery.fileupload.css">
<script src="http://libs.baidu.com/jquery/2.1.1/jquery.min.js"></script>
<script src="./jquery-file-upload/js/vendor/jquery.ui.widget.js"></script>
<script src="./jquery-file-upload/js/jquery.iframe-transport.js"></script>
<script src="./jquery-file-upload/js/jquery.fileupload.js"></script>

<form id="uploadForm" method="post" enctype="multipart/form-data">
    <input name="key" type="hidden" value="<?= $clientToken['key'] ?>">
    <input name="token" type="hidden" value="<?= $clientToken['token'] ?>">
    <input type="file" name="file" class="jquery-file-upload">
</form>

<!-- 进度条 -->
<div id="jquery-file-upload-progress" class="progress progress-striped active" style="display: none;">
    <div class="progress-bar progress-bar-info"></div>
</div>

<div class="show-images"></div>

<script>
    $(function () {
        'use strict';
        $('.jquery-file-upload').fileupload({
            url: "http://upload.qiniu.com/",
            dataType: 'json',
            done: function (e, data) {

                //console.log(data.result)

                //上传完成后 隐藏进度条
                $("#jquery-file-upload-progress").hide();

                var imgUrl = "<?php echo $baseUrl?>" + data.result.key;
                //$("<img>").attr('src',imgUrl).appendTo($(".show-images"));

                alert("上传成功:" + imgUrl);

            },
            progressall: function (e, data) {
                //显示进度条
                $("#jquery-file-upload-progress").show();
                var progress = parseInt(data.loaded / data.total * 100, 10);
                $('#jquery-file-upload-progress .progress-bar').css('width', progress + '%');
            }
        }).prop('disabled', !$.support.fileInput).parent().addClass($.support.fileInput ? undefined : 'disabled');
    });

</script>

</body>
</html>