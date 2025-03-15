<?php

    $f3 = Base::instance();

    $modules = [
        'discover' => 'Discover'
    ];

    foreach($modules as $key => $module) {
        $active = $f3->module == $key ? ' active': '';
        echo "<a href=\"{$key}\" class=\"item{$active}\">{$module}</a>";
    }
?>
