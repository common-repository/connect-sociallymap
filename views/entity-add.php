<div class="wrap">
    <h1>Ajouter une nouvelle entité</h1>
    <h1>
        <a href="?page=sociallymap-entity-list" class="page-title-action">
            Retour à la liste.
        </a>
    </h1>
    <div>
        <form action="<?php echo admin_url('admin.php?page=sociallymap-entity-add'); ?>" method="POST">
            <?php wp_nonce_field('entity-add-', 'nonce-field'); ?>
            <input type="hidden" name="form_entity_add_sent" value="1"/>
            <?php
            $this->includeView('partial/entityForm', $formData);
            ?>
        </form>
    </div>
</div>
