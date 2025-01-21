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
        <link href="https://fonts.googleapis.com/css2?family=Bowlby+One+SC&family=Roboto+Condensed:ital,wght@0,100..900;1,100..900&family=Roboto+Slab:wght@100..900&display=swap" rel="stylesheet">

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
    <!-- <div id="loader">
        <svg id="camecc" width="400" height="120" viewBox="0 0 200 60" version="1.1">
            <path id="logo_1" d="M 25.90595,3.5416303 C 11.598519,3.5416303 1.9582462e-6,15.387471 0,29.99997 -2.9373664e-6,44.612479 11.598516,56.4583 25.90595,56.4583 H 38.858925 V 49.843719 H 25.90595 c -10.730574,0 -19.4294619,-8.88437 -19.4294619,-19.84375 2e-6,-10.959378 8.6988899,-19.843748 19.4294619,-19.843748 7.696581,-9e-5 14.667736,4.6399 17.775428,11.83132 l 3.695646,-6.7908 C 42.560618,7.9095403 34.514479,3.5418703 25.90595,3.5416303 Z" />
            <path id="logo_2" d="M 58.369344,6.9931003 35.047918,49.84372 l 3.809995,6.61458 9.79e-4,-0.002 19.51042,-35.848449 V 47.1039 l -4.505714,-4.60178 -3.272143,6.01255 7.777857,7.9437 h 6.476487 V 6.9931703 Z" />
            <path id="logo_3" d="M 71.322314,6.9936203 V 56.44796 h 6.476494 V 20.609321 l 5.18321,9.523449 c 0.09732,0.0661 6.478002,0 6.478002,0 l 5.18322,-9.523449 V 56.44796 h 6.47648 V 6.9936203 h -6.47648 v 0.001 L 86.221279,22.470161 77.798808,6.9946203 v -0.001 h -5.09e-4 z" />
            <path id="logo_4" d="m 107.59622,8.4074803 v 6.6145907 h 25.90594 V 8.4074803 Z m 0,21.4245297 v 6.61459 h 12.95297 v -6.61459 z m -7.51274,20.0117 v 6.61459 h 33.41868 v -6.61459 z" />
            <path id="logo_5" d="m 157.71815,6.9925803 c -13.37419,1.5e-4 -24.21607,11.0734307 -24.21599,24.7328597 -8e-5,13.659429 10.8418,24.73271 24.21599,24.73286 h 5e-4 3.00044 9.95254 0.51509 a 22.667705,23.151041 0 0 1 -1.02562,-1.096061 l -0.0238,-0.0413 a 22.667705,23.151041 0 0 1 -3.56561,-5.47719 h -0.004 -5.84907 -3.00044 -5e-4 c -9.79733,-1.61e-4 -17.73958,-8.11199 -17.73951,-18.11827 -8e-5,-10.006279 7.94217,-18.118119 17.73951,-18.118279 6.2985,-7e-5 11.83001,3.35261 14.97789,8.405689 a 22.667705,23.151041 0 0 1 5.54852,-3.40961 C 173.89451,11.503911 166.20158,6.9931003 157.71815,6.9925803 Z" />
            <path id="logo_6" d="m 187.04702,23.385381 c -8.94216,0 -16.19121,7.403629 -16.19121,16.536459 0,9.13283 7.24905,16.53646 16.19121,16.53646 H 200 v -0.20981 l -3.68907,-6.40478 h -7.40596 c 0,0 -1.51851,-0.002 -1.85795,0 -5.3653,0 -9.71472,-4.44217 -9.71472,-9.92187 0,-5.4797 4.34942,-9.92188 9.71472,-9.92188 4.07408,5.4e-4 7.71505,2.597279 9.1187,6.50348 l 1.4142,-2.59829 2.24856,-4.13205 c -3.06699,-4.029679 -7.78408,-6.387119 -12.78146,-6.387719 z" />
        </svg>
    </div> -->
    <?php include('assets/php/header.php'); ?>
    <main>
        <section id="banner">
            <h1><strong>Boas-vindas, cameccer!</strong></h1>
            <p>O CAMECC é o <strong>Centro Acadêmico dos Estudantes do IMECC</strong>, uma entidade criada por alunos e <em>para alunos</em> do IMECC. Nossa missão é representar e articular o corpo discente do instituto.</p>
            <figure class="cover">
                <img src="/~camecc/assets/img/sinuca_perspectiva.jpg" alt="Fotografia da Sede do Camecc em um dia qualquer de 2024. A foto é tirada em perspectiva a partir da mesa de sinuca, que é vermelha. À direita, há um homem em pé. À esquerda há um braço com um taco de sinuca pronto para dar uma tacada. Ao fundo, várias pessoas conversam.">
            </figure>
        </section>
        <section>
            <svg height="0">
                <defs>
                    <clipPath id="wave" clipPathUnits="objectBoundingBox">
                        <path d="M0,0.5L0.0556,0.4334C0.1111,0.3656,0.2222,0.2344,0.3333,0.2834C0.4444,0.3344,0.5556,0.5656,0.6667,0.6C0.7778,0.6344,0.8889,0.4656,0.9444,0.3834L1,0.3L1,1L0.9444,1C0.8889,1,0.7778,1,0.6667,1C0.5556,1,0.4444,1,0.3333,1C0.2222,1,0.1111,1,0.0556,1L0,1Z" />
                    </clipPath>
                </defs>
            </svg>
            <h2>São finalidades do CAMECC (segundo nosso <a href="/~camecc/docs/estatuto.pdf">Estatuto</a>):</h2>
            <ol>
                <li><p><em>Defender os interesses e direitos dos estudantes</em> dos cursos de Graduação do IMECC, sem qualquer distinção de raça, cor, nacionalidade, sexo, ou convicção política, religiosa ou social;</p></li>
                <li><p><strong>Manifestar-se publicamente</strong>, sempre que necessário, em nome dos estudantes representados, se solidarizando com as <em>reivindicações dos estudantes e das entidades</em> estudantis;</p></li>
                <li><p>Manter <strong><em>contato e atividades conjuntas</em> com associações congêneres</strong>, sempre que necessário e conveniente aos interesses e aspirações dos seus Associados representados;</p></li>
                <li><p>Participar e desenvolver <strong>atividades socialmente responsáveis</strong>.</p></li>
            </ol>
        </section>
        <section id="events">
            <h2>Próximos Eventos</h2>
            <section class="date-list">
                <?php

                    include('assets/php/iCal.php');

                    $iCal = new ICal('https://calendar.google.com/calendar/ical/camecc%40unicamp.br/public/basic.ics');

                    $events = $iCal->eventsByDateSince(strtotime("now"));

                    if ($events) {

                        $count = 0;

                        while ($count < 6) {
                            foreach ($events as $date => $events)
                            {
                                foreach ($events as $event)
                                {
                                    echo '<article>';
                                    echo '<p class="date">' . date_format(date_create($date),"d/m/Y");
                                    if ($event->duration() > 86400) { echo ' – ' . date_format(date_sub(date_create($event->dateEnd),date_interval_create_from_date_string("1 second")),"d/m/Y");};
                                    echo '</p>';
                                    echo '<p>' . $event->title() . "</p>";
                                    echo "</article>";
                                }  
                                $count += 1;
                            }
                        }

                    } else {

                        echo '<p class="warning">Não há eventos próximos!</p>';

                    }

                ?>
            </section>
        </section>
        <hr>
        <!-- <section id="highlights">
            <h2>Destaques e Notícias</h2>
            <section class="gallery">
                <article>
                    <img src="" alt="">
                    <h3></h3>
                    <p></p>
                </article>
                <article>
                    <img src="" alt="">
                    <h3></h3>
                    <p></p>
                </article>
                <article>
                    <img src="" alt="">
                    <h3></h3>
                    <p></p>
                </article>
            </section>
        </section> -->
        <section id="log">
            <h2>Saídas de Reunião</h2>
            <ul>
                <li><a href="https://docs.google.com/document/d/1mpKcrm3Dqac_TbbewlhBitg6lx7nmaiLwliRnjp1p8M/edit">Plenária: Novo Ensino Médio (25/04)</a></li>
                <li><a href="https://docs.google.com/document/d/1PCjQPi4RlQj5C6ILujQbXUavMMZb098_38oopdYlmU4/edit">Conversa Aberta: Saídas da Greve (23/04)</a></li>
            </ul>
        </section>
    </main>
    <footer></footer>
    <script src="assets/js/detectScroll.js"></script>
</body>
</html>