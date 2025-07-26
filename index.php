<!DOCTYPE html>
<html lang="pt-BR">
    <head>
        <title>CAMECC - Home</title>
        
        <!-- META TAGS -->
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">

        <!-- SEO -->
        <meta name="author" content="Speck">
        <meta name="description" content="Site Oficial do CAMECC">
        <meta name="keywords" content="centro acadêmico, unicamp, imecc, camecc">
        <link rel="canonical" href="https://ime.unicamp.br/~camecc">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Roboto+Condensed:ital,wght@0,100..900;1,100..900&family=Roboto+Slab:wght@100..900&display=swap" rel="stylesheet">

        <!-- Stylesheets -->

        <link rel="preload" as="style" onload="this.remove();" href="/~camecc/assets/css/loader.css?t=<?php echo date('YmdHis'); ?>" type="text/css">
        <link rel="stylesheet" href="/~camecc/assets/css/loader.css?t=<?php echo date('YmdHis'); ?>" type="text/css">
        
        <link rel="preload" as="style" onload="this.remove();" href="/~camecc/assets/css/header.css?t=<?php echo date('YmdHis'); ?>" type="text/css">
        <link rel="stylesheet" href="/~camecc/assets/css/header.css?t=<?php echo date('YmdHis'); ?>" type="text/css">

        <link rel="preload" as="style" onload="this.remove();" href="/~camecc/assets/css/main.css?t=<?php echo date('YmdHis'); ?>" type="text/css">
        <link rel="stylesheet" href="/~camecc/assets/css/main.css?t=<?php echo date('YmdHis'); ?>" type="text/css">

        <link rel="preload" as="style" onload="this.remove();" href="/~camecc/assets/css/home.css?t=<?php echo date('YmdHis'); ?>" type="text/css">
        <link rel="stylesheet" href="/~camecc/assets/css/home.css?t=<?php echo date('YmdHis'); ?>" type="text/css">
    </head>
    <body id="homepage">
        <?php include "assets/php/header.php" ?>
        <main>
            <section id="hero">
                <h1>o Centro Acadêmico <br class="no-mobile">da<div><span>Matemática</span><span>Estatística</span><span>Comp. Científica</span><span>Computação Científica</span></div></h1>
                <p>é responsável por defender, representar e articular estudantes de graduação do IMECC-Unicamp. Há quase 50 anos, realiza projetos sociais e acadêmicos e luta para garantir uma vivência estudantil mais leve democrática e plural.</p>
                <section id="recent-searches">
                    <article class="search"><a class="clean" href=""><span class="search-icon"></span><p>quero saber mais sobre o camecc</p></a></article>
                    <article class="search"><a class="clean" href=""><span class="search-icon"></span><p>o que o camecc pode fazer por mim</p></a></article>
                    <article class="search"><a class="clean" href=""><span class="search-icon"></span><p>quanto custa o red bull</p></a></article>
                    <article class="search"><a class="clean" href=""><span class="search-icon"></span><p>como posso ajudar</p></a></article>
                </section>
            </section>
            <section id="main-photo">
                <img src="assets/img/main-photo.png" alt="">
            </section>
            <section id="news">
                <!-- Tentar incorporar: https://mmacfadden.substack.com/p/how-to-display-your-substack-feed -->
                <h2>Últimas Publicações</h2>
                <section id="news-grid"></section>
            </section>
            <section id="toolbox">
                <h2>O que você busca?</h2>
                <ul>
                    <li>
                        <a href="">
                            <img src="" alt="" class="icon">
                            <p>acolhimento</p>
                        </a>
                    </li>
                    <li>
                        <a href="">
                            <img src="" alt="" class="icon">
                            <p>eventos e atividades</p>
                        </a>
                    </li>
                    <li>
                        <a href="">
                            <img src="" alt="" class="icon">
                            <p>banco de provas</p>
                        </a>
                    </li>
                    <li>
                        <a href="">
                            <img src="" alt="" class="icon">
                            <p>locação de armários</p>
                        </a>
                    </li>
                </ul>
            </section>
        </main>
        <footer></footer>
        <script src="assets/js/detectScroll.js"></script>
        <script src="assets/js/feedSubStack.js"></script>
    </body>
</head>