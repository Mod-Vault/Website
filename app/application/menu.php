<?php

    $f3 = Base::instance();

    $modules = [
        'discover' => 'Discover'
    ];

    foreach($modules as $key => $module) {
        $active = $f3->module == $key ? ' active': '';
        ?>
            <li class="nav-item">
                <a class="nav-link<?= $active ?>" aria-current="page" href="<?= $key ?>"><?= $module ?></a>
            </li>
        <?php
    }
?>
