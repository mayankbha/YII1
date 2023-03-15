<?php

/**
 * @var $isAdmin bool
 * @var $fileInfo array
 */

use app\assets\AppAsset;
use yii\bootstrap\Html;
use yii\helpers\Url;

AppAsset::register($this);
$this->beginPage();
?>

<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <title><?= Html::encode($this->title) ?></title>
    <meta name="viewport" content="initial-scale=1, maximum-scale=1, user-scalable=no, width=1000, height=1000">
<!--    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">-->
    <link rel="shortcut icon" href="<?= Url::toRoute('/favicon.ico', true);?>" type="image/x-icon" />
    <?= Html::csrfMetaTags() ?>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.14.0/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="<?=Url::toRoute('css/annotation/pdf_viewer.css');?>">
    <link rel="stylesheet" type="text/css" href="<?=Url::toRoute('css/annotation/styles.css');?>">

    <script src="<?=Url::toRoute('js/annotation/pdf.js');?>"></script>
    <script src="<?=Url::toRoute('js/annotation/pdf_viewer.js');?>"></script>
    <script src="<?=Url::toRoute('js/annotation/pdfAnnotate.js');?>"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
</head>

<body>
<?php $this->beginBody() ?>
    <style>
        html body {
            margin: 0;
            min-width: 1000px;
            width: 100%;
            overflow: auto;
        }
        .loader {
            height: 100vh;
            width: 100%;
            background-image: url('<?=Url::toRoute('img/spinner.gif');?>');
            background-position: center;
            background-size: 20%;
            background-repeat: no-repeat;
            background-color: #fff;
            box-shadow: inset 0 0 200px 100px rgba(0,0,0,0.5);
            position: relative;
            z-index: 10000;
            display: block;
        }
        .hide {
            display: none;
        }
    </style>

    <div class="loader"></div>
    <script>
        var tokenName = $('meta[name=csrf-param]').attr("content");
        var token = $('meta[name=csrf-token]').attr("content");
        var originFileName = '<?= $fileInfo['original_file_name'];?>';
        var originFilePK = '<?= $fileInfo['id'];?>';
        var authorAnnotation = {
            name: '<?= Yii::$app->user->identity->account_name;?>',
            id: '<?= Yii::$app->user->identity->getId();?>',
            isAdmin: <?= (int) $isAdmin?>
        };

        var baseUrl = '<?= Yii::$app->urlManager->createAbsoluteUrl('/');?>';
    </script>
<!--	<script src="--><?//=Url::toRoute('js/annotation/script.js');?><!--"></script>-->
	<script src="<?=Url::toRoute('js/annotation/script_es5.js');?>"></script>

    <script>
        showLoader = function () {
            $('.loader').removeClass('hide');
        };

        hideLoader = function () {
            $('.loader').addClass('hide');
        };

        downloadDocumentFragment = function (filePK, fileName, fileSize, offset, chunk, remainingChunk=0, fileData=null) {
            var me = this;
            var $data = {'filePK' : filePK, 'fileName': fileName, 'fileSize': fileSize, 'offset': offset, 'chunk': chunk, 'remainingChunk': remainingChunk, 'fileData': fileData};
                $data[tokenName] = token;

            $.ajax({
                type: 'POST',
                cache: false,
                url: '<?= Url::toRoute(['/file/document-download-fragment']);?>',
                data: $data,
                success: function (data) {
                    if(data.remainingChunk != 0) {
                        me.downloadDocumentFragment(filePK, fileName, data.fileSize, data.offset, data.chunk, data.remainingChunk, data.fileData);
                    } else if(data.status == 'success') {
                        if (data.response.file_data) {
                            const body = {profilepic:"data:image/png;base64,"+data.response.file_data};
                            let mimeType = body.profilepic.match(/[^:]\w+\/[\w-+\d.]+(?=;|,)/)[0];

                            me.documentDownload(fileName, mimeType, data.response.file_data, data.fileData, filePK);
                        }
                    }
                }
            });
        }

        documentDownload = function (fileName, mimeType, fileData, fileData2, fileId) {
            var me = this;
            var $data = {'fileName': fileName, 'mimeType': mimeType, 'fileData': fileData, 'fileData2': fileData2, 'fileId': fileId};
                $data[tokenName] = token;

            $.ajax({
                type: 'POST',
                cache: false,
                url: '<?= Url::toRoute(['/file/document-download']);?>',
                data: $data,
                success: function (data) {
                    hideLoader();
                    if(data.status == 'success') {
                        let $data = {
                            url: data.url,
                            originFileName: originFileName,
                            authorAnnotation: authorAnnotation,
                            saveToDataBase: function() {
                                let extended_pdf = pdfFactory.write();
                                let blob = new Blob([extended_pdf], { type: "application/pdf" });
                                me.documentUpload(blob);
                            }
                        };
                        createViewer($data);
                    } else if(data.status == 'error') {
                        alert(data.message);
                    }
                }
            });
        };

        documentUpload = function (myBlob) {
            var me = this;
            var data = new FormData();
            data.append(tokenName, token);
            data.append("file", myBlob, originFileName);
            data.append("category", '<?= $fileInfo['document_category'];?>');
            data.append("family", '<?= $fileInfo['document_family'];?>');

            showLoader();

            $.ajax({
                type: 'POST',
                url: '<?= Url::toRoute(['/file/document-upload']);?>',
                data: data,
                contentType: false,
                cache: false,
                processData: false,
                success: function (response) {
                    if(response) {
                        var $data = {
                            fileName: originFileName,
                            filePK: originFilePK,
                        };
                        $data[tokenName] = token;

                        $.ajax({
                            type: 'POST',
                            cache: false,
                            url: '<?= Url::toRoute(['/file/document-update-init-upload']);?>',
                            data: $data,
                            success: function (data) {
                                if (data.status == 'success') {
                                    me.uploadDocumentFileFragment(me, originFileName, data.response, 0, 1);
                                } else if(data.status == 'error') {
                                    hideLoader();
                                    alert(data.message);
                                }
                            },
                            error: function (data) {
                                hideLoader();
                                alert(data.message);
                            }
                        });
                    }
                },
                error: function (response) {
                    hideLoader();
                    alert(data.message);
                }
            });
        };

        uploadDocumentFileFragment  = function (object, fileName, initResponse, offset, chunk) {
            var me = this;
            var $data = {pk: initResponse['file_container_pk'], file_name: fileName, offset: offset, chunk: chunk};
            $data[tokenName] = token;
            $.ajax({
                type: 'POST',
                cache: false,
                url: '<?= Url::toRoute(['/file/document-upload-fragment']);?>',
                data: $data,
                success: function (data) {
                    if (data.status == 'completed') {
                        me.uploadDocumentFileFragment(object, fileName, initResponse, data.response.offset, data.response.chunk);
                    } else if (data.status == 'success') {
                        var $data = {pk: initResponse['file_container_pk']};
                        $data[tokenName] = token;

                        $.ajax({
                            type: 'POST',
                            cache: false,
                            url: '<?= Url::toRoute(['/file/document-finish-upload']);?>',
                            data: $data,
                            success: function (data) {
                                hideLoader();
                                if (data.status == 'success') {
                                    alert('The file has been successfully updated.');
                                } else if(data.status == 'error') {
                                    alert(data.message);
                                }
                            }
                        });
                    } else if (data.status == 'error') {
                        hideLoader();
                        alert(data.message);
                    }
                },
                error: function (data) {
                    hideLoader();
                    alert(data.message);
                }
            });
        };

        window.onload = function() {
            downloadDocumentFragment(<?= $fileInfo['id'];?>, '<?= $fileInfo['original_file_name'];?>', <?= $fileInfo['original_file_size'];?>, 0, <?= $fileInfo['chunk_size'];?>, 0);
        }
    </script>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>