<?php
// Detecta o ambiente automaticamente pelo domínio
if ($_SERVER['HTTP_HOST'] === 'localhost') {
    // Ambiente de desenvolvimento
    $HOME = '/home/entidades/camecc/public_html/';
} else {
    // Ambiente de produção (IMECC)
    $HOME = '/~camecc/';
}
?>