<ul class="subsubsub">
    <li>
        <?php
        if ($_GET['page'] === 'sociallymap-entity-list') {
            ?>
            <span>
                 Mes entités
             </span>
            <?php
        } else {
            ?>
            <a href="?page=sociallymap-entity-list">
                Mes entités
            </a>
            <?php
        }
        ?>
    </li>
    <li>
        <?php
        if ($_GET['page'] === 'sociallymap-entity-add') {
            ?>
            <span>
                Ajouter une entité
             </span>
            <?php
        } else {
            ?>
            <a href="?page=sociallymap-entity-add">
                Ajouter une entité
            </a>
            <?php
        }
        ?>
    </li>
</ul>
