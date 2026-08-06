<?
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');
$APPLICATION->SetTitle("Редирект");
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="robots" content="noindex, nofollow">
    <title>Document</title>
    <style>
        body {
            margin: 0;
        }
        .site-na-bx-redirect-container {
            width: 100%;
            min-height: 100vh;
            margin: 0;
            padding: 0;
        }
        .site-na-bx-redirect-container .messageBox {
            margin: 10px auto;
            padding: 25px;
            max-width: 500px;
            border: 1px solid #505050;
            border-radius: 5px;
            box-sizing: border-box;
            font-size: 90%;
            text-align: center;
        }
        .site-na-bx-redirect-container .messageBox > a {
            color: #0084e6;
            text-decoration: none;
        }
        .site-na-bx-redirect-container .messageBox > a:hover {
            color: #0084e6;
            text-decoration: underline;
        }
        .-fancyBg {
            background-color: #fff;
        }
        .-messBg {
            background-color: #dadada;
        }
    </style>
</head>
<body>
<div class="site-na-bx-redirect-container -fancyBg">
    <div class="messageBox -messBg">
        <?php
        $itwebRedirect = new \App\Itweb\Services\Redirect();
        if (isset($_GET['goto']) && !empty($_GET['goto'])) {
            $goto = $_GET['goto'];
            $siteName = explode('/',$goto)[2];
            if (isset($_GET['key']) && $itwebRedirect->isUrlCorrect($_GET['key'], $_GET['goto'])) {
                if (isset($_GET['si']) && !empty($_GET['si'])) {
                    if ($_GET['si'] == $itwebRedirect->getCurrentSessionId()) {
                        header("Location: {$_GET['goto']}");
                    } else {?>
                        Вы собираетесь пройти по ссылке на сайт:
                        <a href="<?=$_GET['goto'];?>" rel="nofollow"><?=$siteName;?></a><br>
                        Подтвердите данное действие.
                        <a href="<?=$_GET['goto'];?>" rel="nofollow">Перейти по ссылке</a>
                    <?}
                } else {
                    echo 'Вы попытались перейти по нерабочей ссылке';
                }
            } else {
                echo 'Вы попытались перейти по нерабочей ссылке';
            }
        } else {
            echo 'Вы попытались перейти по нерабочей ссылке';
        }
        ?>
    </div>
</div>
</body>
</html>
<?php require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_after.php');?>
