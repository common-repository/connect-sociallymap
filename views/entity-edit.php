<div class="wrap">
    <h1>Editer une entité</h1>
    <h1>
        <a href="?page=sociallymap-entity-list" class="page-title-action">
            Retour à la liste.
        </a>
    </h1>
    <div>
        <form action="<?php echo admin_url('admin.php?page=sociallymap-entity-edit&id=' . $formData['entityData']['id']); ?>"
              method="POST">
            <?php wp_nonce_field('entity-edit-', 'nonce-field'); ?>
            <input type="hidden" name="form_entity_edit_sent" value="1"/>
            <input type="hidden" name="entity[id]" value="<?php echo $formData['entityData']['id']; ?>"/>
            <?php
            $this->includeView('partial/entityForm', $formData);
            ?>
        </form>
    </div>
</div>
