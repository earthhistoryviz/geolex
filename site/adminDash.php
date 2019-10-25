<?php
//include("page1.html");
//?>
<!--<!DOCTYPE html>-->
<!--<html>-->
<!--<head>-->
<!--    <meta charset="utf-8"/>-->
<!--    <title>index</title>-->
<!---->
<!--    <link rel="stylesheet" type="text/css" href="style.css"/>-->
<!---->
<!--</head>-->
<!--<body>-->
<!--<div class="container">-->
    <!-- aside left -->
<!--    <div class="aside-left">-->
<!--        <ul>-->
<!--            <li>-->
<!---->
<!--                <a href="##" target="main" class="m-title">Dashboard</a>-->
<!--            </li>-->
<!--            <li>-->
<!--                <a href="page1a.html" target="main" >Manage User information</a>-->
<!--            </li>-->
<!--            <li>-->
<!--                <a href="adminDash.php" target="main" class="active">Manage Database</a>-->
<!--            </li>-->
<!---->
<!--        </ul>-->
<!--    </div>-->
    <!-- aside right -->
<!--    <div class="aside-right">-->
<!--        <div class="top">-->
<!--            <a href="##" class="logout">Logout</a>-->
<!--            <h1 class="fl tit">Welcome to Dashbord</h1>-->
<!--            <p class="fr user">User:Yuanhao</p>-->
<!--        </div>-->
<!--        <div class="main">-->
<!--            <div class="db">-->
<!--                <h2 class="h2">Manage Database</h2>-->
<!--            </div>-->
<!--            --><?php //include("SearchBar.php"); ?>
<!--            <div>-->
<!--            </div>-->
<!--        </div>-->
<!---->
<!--    </div>-->
<!--</div>-->
<!--</div>-->
<!--</body>-->
<!--</html>-->

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8"/>
    <title>index</title>

    <link rel="stylesheet" type="text/css" href="style.css"/>

</head>
<body>
<div class="container">
    <!-- aside left -->
    <div class="aside-left">
        <ul id="menus">
            <li>

                <a href="javascript:;" class="m-title active">Dashboard</a>
            </li>
            <li class="item active">
                <a href="javascript:;">Manage User information</a>
            </li>
            <li class="item">
                <a href="javascript:;">Manage Database</a>
            </li>

        </ul>
    </div>
    <!-- aside right -->
    <div class="aside-right" id="conts">
        <div class="top">
            <a href="##" class="logout">Logout</a>
            <h1 class="fl tit">Welcom to Dashbord</h1>
            <p class="fr user">User:Yuanhao</p>
        </div>

        <div class="item" style="display:block">
            Manage User information
        </div>
        <div class="item">

            <div class="main">
                <div class="db">
                    <h2 class="h2">Manage Database</h2>
                    <?php include("SearchBar.php"); ?>
                </div>

            </div>
        </div>
    </div>
    <div class="main">

    </div>
</div>
<script src="https://cdn.bootcss.com/jquery/3.4.1/jquery.min.js"></script>
<script>
    $(function(){
        var menus = $('#menus').find('.item');
        var conts = $('#conts').find('.item');
        menus.click(function(){
            $(this).addClass('active').siblings('.item').removeClass('active');
            conts.eq($(this).index() - 1).show().siblings('.item').hide();
        })
    })
</script>
</body>
</html>

