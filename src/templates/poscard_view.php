<?php
if(isset($_GET['photo'])){
    $photo_exists = true;
} else {
    $photo_exists = false;
}

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8"/>
    <title>Редактирование открыток</title>
    <link rel="stylesheet" type="text/css" href="Content/jquery.Jcrop.min.css"/>
    <meta name="viewport" content="width=device-width"/>
    <link href="Content/css%3Fv=auXQHq_MJ9PpXuMua4OdOXsxGw9Bf2CmJfMIyEbXDNg1.css" rel="stylesheet"/>
    <link href="Content/themes/base/css%3Fv=sqgI9VF3AO9Gpvzgmqf9ElwZsnZmdaHcLCLXK3gV6nU1.css" rel="stylesheet"/>
    <script src="bundles/jquery%3Fv=37cfAnNlsc0DRT6NbRj2m9jH9p2KI8RM1_wA0IiL9AQ1"></script>
    <script src="bundles/jqueryui%3Fv=HJ0NlUzFZbeFoMVnfSJBFm3sLd8ji3fNdIoWgXqNMtk1"></script>
    <script src="bundles/touchpunch%3Fv=Umnfui5W_RvW8eWPqfDmyCivIBTTSoX8wxjhRD6amwA1"></script>
    <script src="bundles/jqueryExtra%3Fv=mZiHYvcxt04K6egsnWxTty0y6r-mTBU09hjHOimaFuA1"></script>
    <script src="bundles/modernizr%3Fv=jmdBhqkI3eMaPZJduAyIYBj7MpXrGd2ZqmHAOSNeYcg1"></script>
    <script src="Scripts/jquery.Jcrop.min.js" type="text/javascript"></script>
    <script src="Scripts/html2canvas.js" type="text/javascript"></script>
    <style>
        .change_fields {
            width: 250px;
        }
    </style>
</head>
<body>

<script type="text/javascript">
    var jcapi;
    var messageShown;

    $(function () {
        messageShown = false;

        $(".button").button();
        jcapi = $.Jcrop('.photoEditor .mainPhoto img');
        $(".photoClickArea").css('opacity', '0');
        $(".photoClickArea").css('filter', 'alpha(opacity=0)');
        $(".dialog").dialog({
            autoOpen: false,
            height: 400,
            width: 600,
            modal: true,
            buttons: {
                Cancel: function () {
                    $(this).dialog("close");
                }
            }
        });

        $(".photoEditor").dialog({
            autoOpen: false,
            height: 670,
            width: 895,
            modal: true,
            title: 'Edit your Photo',
            buttons: [
                {
                    text: "",
                    click: function () {
                        var $mp = $('.photoEditor .mainPhoto');
                        var $pa = $('#photo_' + $(this).data('photoNum'));
                        var src = $mp.data('workingurl') + '?crop=' + $mp.data('crop') + '&bw=' + $mp.data('grayscale') + '&sp=' + $mp.data('sepia');
                        setPhoto($pa, $('<img class="pcImg photoImg" data-original="' + $pa.data('original') + '" src="' + src + '">'));
                        $(this).dialog('close');
                    },
                    "class": 'dlgSaveButton'
                }
            ]
        });
        $(".photoEditor").dialog({ dialogClass: 'photoEditorDialog' });
        $(".draggable").draggable({
            cancel: "a.ui-icon", // clicking an icon won't initiate dragging
            revert: "invalid", // when not dropped, the item will revert back to its initial position
            containment: "document",
            helper: "clone",
            cursor: "move",
            appendTo: 'body'
        });

        $(".photoArea").droppable({
            accept: ".photoImg",
            activeClass: "ui-state-highlight",
            drop: function (event, ui) {
                var oi = ui.draggable.data('original');
                $(this).data('original', oi);
                setPhoto($(this), $('<img class="pcImg photoImg" data-original="' + oi + '" src="' + oi + '">'));
                showEditor($('#pc_' + $(this).data('photonum')));
            }
        });

        $(".btnPhotoUpload img").click(function () {
            var dlg = 'dlgPhotoUpload';
            $('#' + dlg).dialog('option', 'title', $('#' + dlg).data('title'));
            $('#' + dlg).dialog('open');
        });

        $('.openDialog').click(function (e) {
            var dlg = $(this).data('dlgtoopen');

            $('#' + dlg).dialog('option', 'title', $('#' + dlg).data('title'));
            $('#' + dlg).dialog('open');
        });

        $('.btnSepia').click(function (e) {
            var $mp = $('.photoEditor .mainPhoto');
            $mp.data('sepia', $mp.data('sepia') * -1);
            $mp.data('grayscale', -1);
            setPreview();
        });
        $('.btnGrayScale').click(function (e) {
            var $mp = $('.photoEditor .mainPhoto');
            $mp.data('grayscale', $mp.data('grayscale') * -1);
            $mp.data('sepia', -1);
            setPreview();
        });
        $('.btnOriginal').click(function (e) {
            var $mp = $('.photoEditor .mainPhoto');
            $mp.data('grayscale', -1);
            $mp.data('sepia', -1);
            setPreview();
        });

        $(".btnSaveDesign").click(function () {
            SaveDesign();
        });

        $('.productText').each(function () {
            $(this).data('oldVal', $(this).val());

            $(this).bind("propertychange keyup input paste", function (event) {
                if ($(this).data('oldVal') != $(this).val()) {
                    $(this).data('oldVal', $(this).val());
                    $(this).attr("value", $(this).val());
                    UpdateText($(this).data('id'), $(this).data('width'), $(this).data('height'));
                }
            });
        });

        $('.uploadDialog').dialog({
            autoOpen: false,
            modal: true,
            width: 450,
            title: 'Upload Status'
        });

        var bar = $('.bar');
        var percent = $('.percent');
        var status = $('#status');

        $('#photoUploadForm').ajaxForm({
            beforeSend: function () {
                $('.uploadDialog').dialog('open');
                status.empty();
                var percentVal = '0%';
                bar.width(percentVal);
                percent.html(percentVal);
            },
            uploadProgress: function (event, position, total, percentComplete) {
                var percentVal = percentComplete + '%';
                bar.width(percentVal);
                percent.html(percentVal);
            },
            complete: function (xhr) {
                var filename = xhr.responseText;
                addImgToManager("http://images.invitationbox.com/CustomerImages/" + filename);

                $(".dialog").dialog('close');
                $('.uploadDialog').dialog('close');
                setImageCookie();
            }
        });

        $('.photoClickArea').click(function (event) {
            showEditor($(this));
        });



        //добавлено, для сохранения записей
        $('#saveLines').click(function () {
            var postcardFrameNew = document.getElementById('productFrame').innerHTML;
            var postcardTextInputNew = document.getElementById('postcard_textInput').innerHTML;
            document.getElementById('NewPostcardFrame').value = postcardFrameNew;
            document.getElementById('NewPostcardTextInput').value = postcardTextInputNew;
            document.savecard_line.submit();
        });

        //addImgToManager('http://images.invitationbox.com/CustomerImages/9a30c3d3b84d4b86a76f6113169fdbd5.jpg');
        //addImgToManager('http://images.invitationbox.com/CustomerImages/938992a121064c72a79e471c7ecfe7c0.jpg');
    });
    function addImgToManager(imgUri) {
        var img = $('<img class="pcImg photoImg setCookie">');
        img.attr('src', imgUri + '?max=125');
        img.data('original', imgUri);
        img.draggable({
            cancel: "a.ui-icon", // clicking an icon won't initiate dragging
            revert: "invalid", // when not dropped, the item will revert back to its initial position
            containment: "document",
            helper: "clone",
            cursor: "move",
            appendTo: 'body'
        });
        $pc = $('#photoContainerTemplate').clone(true);
        $pc.attr('id', imgUri);
        $pt = $pc.find('.photoThumb');
        $pt.append(img);
        $pm = $('.photoManager');
        $pm.append($pc);
    }
    function setPreview(w, h) {
        $mp = $('.photoEditor .mainPhoto');
        $pp = $('.photoEditor .previewPhoto');
        $pp.html('');
        var src = $mp.data('workingurl');
        var crop = $mp.data('crop');
        var sp = $mp.data('sepia');
        var bw = $mp.data('grayscale');
        var psize = '375';

        src += '?max=' + psize;
        src += '&bw=' + bw + '&sp=' + sp + '&crop=' + crop;
        $pp.append($('<img src="' + src + '">'));
    }
    function setPhoto($dropArea, $img) {
        if ($dropArea.height() > $dropArea.width()) {
            $img.height($dropArea.height());
        } else {
            $img.width($dropArea.width());
        }
        $dropArea.html('');
        $dropArea.append($img);
    }
    function showEditor($clickArea) {
        var pn = $clickArea.data('photonum');
        var imgUrl = $('#photo_' + pn + ' img').data('original');
        var $mp = $('.photoEditor .mainPhoto');
        var aspect = $clickArea.width() / $clickArea.height();
        var max = 375;

        jcapi.setOptions({ onSelect: updateCoords, aspectRatio: aspect });
        jcapi.setImage(imgUrl + "?max=375", function () {
            jcapi.setSelect([0, 0, max, max]);
            updateCoords(jcapi.tellSelect());
        });

        $mp.data('workingurl', imgUrl);
        $('.photoEditor').data('photoNum', pn);
        $('.photoEditor').dialog('open');
        //checkBounds($clickArea);
    }
    function checkBounds($ca) {
        $p = $(".selectedProduct");
        var ph = $p.height();
        var pw = $p.width();
        var l = parseInt($ca.css('left').replace('px',''));
        var t = parseInt($ca.css('top').replace('px',''));
        var ch = $ca.height() + t;
        var cw = $ca.width() + l;

        if ((l <= 0 || t <= 0 || cw >= pw || ch >= ph) && !messageShown) {
            messageShown = true;
            alert('bleed message');
        }
    }
    function UpdateText(ptlId, w, h) {
        $tb = $("#tb_" + ptlId);
        $text = $("#txt_" + ptlId);
        $text.html('');

        var img = $('<img class="textImg" id="timg_' + ptlId + '">'); //Equivalent: $(document.createElement('img'))
        img.attr('src', "http://www.theprintingbox.com/FontTest/lineGenerator.aspx?line=" + encodeURIComponent($tb.val()) + "&font=" + $text.data('font') + "&size=" + $text.data('size') + "&align=" + $text.data('align') +"&color=" + $text.data('color') + "&width=" + w + "&height=" + h);
        $text.append(img);
    }
    function SaveDesign() {
        var saveString = 'pid=' + $(".selectedProduct").attr('id');

        $(".photoArea").each(function (cx, e) {
            $mp = $(this).find('.photoImg');
            saveString += '|$|' + $(this).attr('id') + '_img=' + $mp.attr('src');
        });

        $(".productText").each(function (cx, e) {
            saveString += '|$|' + $(this).attr('id') + '_text=' + encodeURIComponent($(this).val());
        });

        $('#SaveConfig').val(saveString);
        $('#AddForm').submit();
    }
    function setImageCookie() {
        var cookieStr = '';
        var sep = '';
        $(".setCookie").each(function (cx, e) {
            cookieStr += sep + $(this).data('original');
            sep = '|$|';
        });
        $.cookie('pmCookie', cookieStr);
    }
    function updateCoords(c) {
        var $mp = $('.photoEditor .mainPhoto');
        var $img = $mp.find('.jcrop-holder img');
        var max = $img.height() > $img.width() ? $img.height() : $img.width();
        var w = parseInt(c.w);
        var h = parseInt(c.h);
        var crop = parseInt(c.x) + '|' + parseInt(c.y) + '|' + w + '|' + h + '|' + max;
        $mp.data('crop', crop);
        setPreview(w, h);
    };

</script>

<div class="bodyContainer">

    <div class="bodyContent">
        <div class="productConfigurator">
            <div style="float: left; width: 10px; height: 10px;">&nbsp;</div>
            <div class="configureContainer">
                <div class="productFrameContainer">
                    <div id="productFrame" class='productFrame'>

                        <?=$ticket->getFrame()?>
                        </div>
                    <div class="productFrameSpacer">&nbsp;</div>
                </div>
                <div class="productSpacer left">&nbsp;</div>
                <div class="textFormat left">
                    <div class="rightInstructionsHeader">Инструкция</div>
                    <div class="clear"></div>
                    <div class="horizontalLine">&nbsp;</div>
                    <div class="rightInstructionsText">

                    <?php if($photo_exists): ?>

                        1. Загрузите своё фото<br/>
                        <div class="btnPhotoUpload"><img src="images/upload-photo.png" /></div>
                        2. Переместите свою фото на область с открыткой<br/>
                        3. Кликните на свою фото, чтобы ее отредактировать<br/>
                        4. Введите текст<br/>
                    <?php else: ?>
                        1. Введите текст<br/>
                    <?php endif ?>    
                        <div class="lineContainer">
                            <div class="left" id="postcard_textInput">

                                <?=$ticket->getTextInput()?>
                            </div>
                            <div class="clear"></div>
                        </div>

                    </div>
                    <button id="saveLines">Сохранить перевод</button>
                    <div class="btnSaveDesign"><img src="/images/finished.png" /></div>
                    <div class="clear"></div>
                </div>
                <div class="clear"></div>
                <div class="photoManagerContainer" style="background: url(../images/thatch.png); color: #fff;">
                    <div style="display:inline-block;font-size: 10pt;width:400px; vertical-align: top;">
                        <div class="rightInstructionsHeader">Описание</div>
                        <div class="horizontalLine" style="width:400px;">&nbsp;</div>
                        <b>Id: </b><a href="<?=$ticket->getLink()?>" target="_blank"><?=$ticket->getId()?></a>
                        <b>Статус: </b><?=$ticket->getStatus()?><br/>
                        <b>Название: </b><?=$ticket->getName()?><br/>
                        <b>Категория: </b><?=$ticket->getCatName()?><br/>
                        <b>Описание: </b><?=$ticket->getProductDesc()?><br/>
                        <b>Идеально для: </b> <?=$ticket->getIdealFor()?><br/>
                    </div>
                    <div style="display:inline-block;font-size: 10pt;width:400px; vertical-align: top;">
                        <div class="rightInstructionsHeader">Перевод</div>
                        <div class="horizontalLine"  style="width:400px;">&nbsp;</div>

                        <form method="POST" action="/savecard">
                            <input name="postcard_id" type="hidden" value="<?=$ticket->getId()?>"/>
                            <div class="productTextInput" style="margin: 10px;">
                                <label>Название: </label>
                                <input class="change_fields" name="product_name_ru" type="text" value="<?=$ticket->getNameRu()?>"/><br/>
                                <div style="clear: both;"></div>
                            </div>
                            <div class="productTextInput" style="margin: 10px;">
                                <label>Категория: </label>
                                <input class="change_fields" name="category_name_ru" type="text" value="<?=$ticket->getCatNameRu()?>"/><br/>
                                <div style="clear: both;"></div>
                            </div>
                            <div class="productTextInput" style="margin: 10px;">
                                <label>Описание: </label>
                                <textarea class="change_fields" rows=5 name="product_description_ru"><?=$ticket->getProductDescRu()?></textarea><br/>
                                <div style="clear: both;"></div>
                            </div>
                            <div class="productTextInput" style="margin: 10px;">
                                <label>Идеально для:</label>
                                <input class="change_fields" name="product_idealfor_ru" type="text" value="<?=$ticket->getIdealForRu()?>"/><br/>
                                <div style="clear: both;"></div>
                            </div>
                            <div class="productTextInput" style="margin: 10px;">
                                <label>Статус</label>
                                <select class="change_fields" name="status" >
                                    <option value=""></option>
                                    <option value="ok">ok</option>
                                    <option value="not ok">not ok</option>
                                    <option value="unknown">unknown</option>
                                </select>
                                <div style="clear: both;"></div>
                            </div>
                            <div class="productTextInput" style="margin: 10px;">
                                <input name="postcard_save" type="submit" value="Сохранить"/><br/>
                                <div style="clear: both;"></div>
                            </div>
                        </form>
                    </div>
                </div>
                <?php if($photo_exists): ?>
                    <div class="photoManagerContainer">
                        МОИ ФОТОГРАФИИ<br />
                        <div class="photoManager"></div>
                    </div>
                <?php endif ?>
            </div>
            <div style="clear: both;"></div>
        </div>
    </div>
</div>
<div id="photoContainerTemplate" class="pcTemplate">
    <div class="photoThumb"></div>
</div>
<div class="photoEditor">
    <div class="mainPhotoContainer left">
        <div class="mainPhotoLabel">Выбрать / обрежьте свое фото</div>
        <div class="mainPhoto" data-sepia="-1" data-grayscale="-1">
            <img src="#mp" alt="" />
        </div>
    </div>
    <div class="previewPhotoContainer left">
        <div class="previewPhotoLabel">Предварительный просмотр</div>
        <div class="previewPhoto">
            <img src="#pp" alt="" />
        </div>
    </div>
    <div class="clear"></div>
    <div class="editTools">
        <div class="colorOptions">Color Options</div>
        <div class="btnSepia tool left"><img src="/images/sepia.png" /></div>
        <div class="btnGrayScale tool left"><img src="/images/black-white.png" /></div>
        <div class="btnOriginal tool left"><img src="/images/original-color.png" /></div>
        <div class="clear"></div>
    </div>
    <div class="coords"></div>
</div>

<div class="dialog dlgPhotoUpload" id='dlgPhotoUpload' data-title="Upload a Photo">
    <form id="photoUploadForm" action="/Upload" method="post" enctype="multipart/form-data">
        <div class="">
            Ваша фотография должна быть 1МБ или больше, но не выше 10МБ<br/><br/>
        </div>
        <input type="file" name="photo"><br>
        <input type="submit" value="Загружить фотографию">
    </form>
</div>

<div class="uploadDialog">
    <div class="progress">
        <div class="bar"></div>
        <div class="percent">0%</div>
    </div>
</div>

<form action="/" id="AddForm" method="post"><input id="ReturnId" name="ReturnId" type="hidden" value="" />
    <input id="ReturnURL" name="ReturnURL" type="hidden" value="" />
    <input id="ProductId" name="ProductId" type="hidden" value="bsp-chfiw" />
    <input id="SaveConfig" name="SaveConfig" type="hidden" value="" />
</form>

<form name="savecard_line" action="/savecard_line" id="SaveCardLine" method="post">
    <input name="postcard_id" type="hidden" value="<?=$ticket->getId()?>" />
    <input id="NewPostcardFrame" name="NewPostcardFrame" type="hidden" value="" />
    <input id="NewPostcardTextInput" name="NewPostcardTextInput" type="hidden" value="" />
</form>


</body>

<script type="text/javascript" src="//lib.store.yahoo.net/lib/invitationbox/designer-responsive-scripts.js"></script>
<link type="text/css" rel="stylesheet" href="//lib.store.yahoo.net/lib/invitationbox/designer-responsive-styles.css" />

</html>
