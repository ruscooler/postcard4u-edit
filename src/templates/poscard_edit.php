<?php
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8"/>
    <title>Редактирование открыток</title>
    <link rel="stylesheet" type="text/css" href="Content/jquery.Jcrop.min.css"/>
    <meta name="viewport" content="width=device-width"/>
    <!-- edit cards standart files -->
    <link href="/Content/css%3Fv=auXQHq_MJ9PpXuMua4OdOXsxGw9Bf2CmJfMIyEbXDNg1.css" rel="stylesheet"/>
    <link href="/Content/themes/base/css%3Fv=sqgI9VF3AO9Gpvzgmqf9ElwZsnZmdaHcLCLXK3gV6nU1.css" rel="stylesheet"/>
    <script src="/bundles/jquery%3Fv=37cfAnNlsc0DRT6NbRj2m9jH9p2KI8RM1_wA0IiL9AQ1"></script>
    <script src="/bundles/jqueryui%3Fv=HJ0NlUzFZbeFoMVnfSJBFm3sLd8ji3fNdIoWgXqNMtk1"></script>
    <script src="/bundles/touchpunch%3Fv=Umnfui5W_RvW8eWPqfDmyCivIBTTSoX8wxjhRD6amwA1"></script>
    <script src="/bundles/jqueryExtra%3Fv=mZiHYvcxt04K6egsnWxTty0y6r-mTBU09hjHOimaFuA1"></script>
    <script src="/bundles/modernizr%3Fv=jmdBhqkI3eMaPZJduAyIYBj7MpXrGd2ZqmHAOSNeYcg1"></script>
    <!-- edit cards standart files -->

    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
    <script src="/Scripts/html2canvas.min.js"></script>
    <script src="/Scripts/jquery.plugin.html2canvas.js"></script>

    <style>
        .change_fields {
            width: 250px;
        }
        #page-preloader {
            height: 100%;
            width: 100%;
            /*opacity: 0.7;*/
            /*filter: alpha(Opacity=40);*/
            position: absolute;
            z-index: 100;
            top: 0;
            left: 0;
         }
        #page-preloader .spinner {
            width: 160px;
            height: 24px;
            position: absolute;
            left: 45%;
            top: 50%;
            background: url('/images/76.gif') no-repeat 50% 50%;
            margin: -16px 0 0 -16px;
        }
    </style>
</head>
<body>
<div id="page-preloader" style="display: none"><span class="spinner"></span></div>
<script type="text/javascript">
    var jcapi;
    var messageShown;

    $(function () {
        messageShown = false;

        $(window).on('load', function () {
            var $preloader = $('#page-preloader'),
                $spinner   = $preloader.find('.spinner');
            $spinner.fadeOut();
            $preloader.delay(350).fadeOut('slow');
        });

        $(".btnSaveDesign").click(function () {
            //SaveDesign();

            $('#page-preloader').show();

            $('#map').width($('.selectedProduct').width());
            $('#map').height($('.selectedProduct').height());

            setTimeout(function() {
                $('#map').html2canvas({
                    flashcanvas: "/Scripts/flashcanvas.min.js",
                    proxy: '/proxy',
                    logging: false,
                    profile: false,
                    useCORS: true
                });
            }, 1000);

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

        var bar = $('.bar');
        var percent = $('.percent');
        var status = $('#status');

    });
    function UpdateText(ptlId, w, h) {
        $tb = $("#tb_" + ptlId);
        $text = $("#txt_" + ptlId);
        var text_color = $text.data('color');
        if (text_color == '0') {
            text_color = '000000';
        }
        $text.html('');
        var img = $('<img class="textImg" id="timg_' + ptlId + '">'); //Equivalent: $(document.createElement('img'))

        img.attr('src', "http://www.theprintingbox.com/FontTest/lineGenerator.aspx?line=" + encodeURIComponent($tb.val()) + "&font=" + $text.data('font') + "&size=" + $text.data('size') + "&align=" + $text.data('align') +"&color=" + text_color + "&width=" + w + "&height=" + h);
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
    function manipulateCanvasFunction(savedMap) {
        dataURL = savedMap.toDataURL("image/jpeg");
        productId = '<?=$_GET['productId']?>';
        customerId = '<?=$_GET['customerId']?>';
        dataURL = dataURL.replace(/^data:image\/(png|jpg|jpeg);base64,/, "");
        $.post("/saveMap", { savedMap: dataURL, productId: productId, customerId: customerId }, function(data) {
            $('#page-preloader').find('.spinner').fadeOut();
            $('#page-preloader').delay(350).fadeOut('slow');
            alert('Изображение сохранено! Выберите количество и нажмите кнопку добавить в корзину');

        });
    }
</script>

<div class="bodyContainer">

    <div class="bodyContent">
        <div class="productConfigurator">
            <div style="float: left; width: 10px; height: 10px;">&nbsp;</div>
            <div class="configureContainer">
                <div class="productFrameContainer">
                    <div id="productFrame" class='productFrame'>

                        <div id="map" >
                            <?=$ticket->getFrame()?>
                        </div>
                    </div>
                    <div class="productFrameSpacer">&nbsp;</div>
                </div>
                <div class="productSpacer left">&nbsp;</div>
                <div class="textFormat left">
                    <div class="rightInstructionsHeader">Инструкция</div>
                    <div class="clear"></div>
                    <div class="horizontalLine">&nbsp;</div>
                    <div class="rightInstructionsText">
                        1. Введите текст<br/>
                        <div class="lineContainer">
                            <div class="left" id="postcard_textInput">
                                    <?=$ticket->getTextInput()?>
                            </div>
                            <div class="clear"></div>
                        </div>
                    </div>
                    <div class="btnSaveDesign"><img src="/images/finished.png" /></div>
                    <div class="clear"></div>
                </div>
                <div class="clear"></div>
            </div>
            <div style="clear: both;"></div>
        </div>
    </div>
</div>


<form action="/" id="AddForm" method="post"><input id="ReturnId" name="ReturnId" type="hidden" value="" />
    <input id="ReturnURL" name="ReturnURL" type="hidden" value="" />
    <input id="ProductId" name="ProductId" type="hidden" value="bsp-chfiw" />
    <input id="SaveConfig" name="SaveConfig" type="hidden" value="" />
</form>

<div id="forcanvas">
    <img crossorigin="anonymous" id="imgcanvas"/>
</div>
</body>
<script type="text/javascript" src="//lib.store.yahoo.net/lib/invitationbox/designer-responsive-scripts.js"></script>
<link type="text/css" rel="stylesheet" href="//lib.store.yahoo.net/lib/invitationbox/designer-responsive-styles.css" />

</html>
