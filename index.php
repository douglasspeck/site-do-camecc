<!DOCTYPE html>
<html lang="pt-BR">
    <head>
        <title>CAMECC - Home</title>

        <?php include('assets/php/head.php'); ?>

        <link rel="preload" as="style" onload="this.remove();" href="/~camecc/assets/css/home.css?t=<?php echo date('YmdHis'); ?>" type="text/css">
        <link rel="stylesheet" href="/~camecc/assets/css/home.css?t=<?php echo date('YmdHis'); ?>" type="text/css">

        <!-- SEO -->
        <meta name="author" content="Speck">
        <meta name="description" content="Site Oficial do CAMECC">
        <meta name="keywords" content="centro acadêmico, unicamp, imecc, camecc">
        <link rel="canonical" href="https://ime.unicamp.br/~camecc">
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
</html>