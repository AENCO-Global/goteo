<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js"> <!--<![endif]-->
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <title></title>
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width">

        <meta property="og:title" content="<?php echo $this['ogmeta']['title'] ?>" />
        <meta property="og:type" content="activity" />
        <meta property="og:site_name" content="Goteo.org" />
        <meta property="og:description" content="<?php echo $this['ogmeta']['description'] ?>" />
        <meta property="og:image" content="<?php echo $this['ogmeta']['image'] ?>" />
        <meta property="og:url" content="<?php echo $this['ogmeta']['url'] ?>" />


        <link rel="stylesheet" href="/view/bazar/css/normalize.css">
        <link rel="stylesheet" href="/view/bazar/css/common.css">
        <link rel="stylesheet" href="/view/bazar/css/minimobile.css" media="only screen and (max-width:340px)">
        <link rel="stylesheet" href="/view/bazar/css/mobile.css" media="only screen and (min-width:340px) and (max-width:750px)">
        <link rel="stylesheet" href="/view/bazar/css/tablet.css" media="only screen and (min-width:750px) and (max-width:1024px)">
        <link rel="stylesheet" href="/view/bazar/css/pc.css" media="only screen and (min-width:1024px) and (max-width:1400px)">
        <link rel="stylesheet" href="/view/bazar/css/bigpc.css" media="only screen and (min-width:1400px)">
        
        <script type="text/javascript" src="/view/bazar/js/vendor/modernizr-2.6.2-respond-1.1.0.min.js"></script>
        <script type="text/javascript" src="/view/bazar/js/vendor/jquery-1.10.1.min.js"></script>
    </head>

    <body>
        <script type="text/javascript">
            $(function () {
                $('.activable').hover(
                    function () { $(this).addClass('active') },
                    function () { $(this).removeClass('active') }
                );
            });
        </script>
        <noscript><!-- Please enable JavaScript --></noscript>