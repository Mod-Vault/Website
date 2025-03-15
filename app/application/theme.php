<?php
    $c = [
        'background' => '#282828',

        'primary_background' => '#0dae81',
        'primary_foreground' => '#0A231A',

        'secondary_background' => '#0A231A',
        'secondary_foreground' => '#0dae81',

        'white_background' => '#F4EEEE',
        'white_foreground' => '#000000',

        'red_background' => 'rgb(66, 5, 0)',
        'red_foreground' => 'rgb(222, 61, 61)',
    ];
?>

<style>
    html {
        height: 95vh;
    }
    body {
        background-color: <?= $c['background'] ?>;
    }
    select.selectize {
        width: 100%;
    }

    .secondary.card {
        background-color: <?= $c['secondary_background'] ?> !important;
        color: <?= $c['secondary_foreground'] ?> !important;
    }
    .card .content {
        color: inherit !important;
    }
    .card .content .header {
        color: inherit !important;
    }

    div.ui.primary.segment {
        background-color: <?= $c['primary_background'] ?>;
        color: <?= $c['primary_foreground'] ?>;
    }
    div.ui.secondary.segment {
        background-color: <?= $c['secondary_background'] ?>;
        color: <?= $c['secondary_foreground'] ?>;
    }
    div.ui.white.segment {
        background-color: <?= $c['white_background'] ?>;
        color: <?= $c['white_foreground'] ?>;
    }
    div.red.message {
        background-color: <?= $c['red_background'] ?> !important;
        color: <?= $c['red_foreground'] ?> !important;
    }
</style>
