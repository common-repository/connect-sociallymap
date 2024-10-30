<?php

use SociallymapConnect\Configs\PluginConfig;
use SociallymapConnect\Includes\Enum;

if ($onError) { ?>
    <div class="error notice is-dismissible error settings-error" ><p><?php echo __('Une erreur est survenue lors de l\'enregistrement de l\'entité', PluginConfig::DOMAIN_TRANSLATE)?></p></div>
<?php } ?>
<table class="form-table">
    <tbody>
        <tr class="form-field">
            <th>
                <label><?php echo __('Entité active', PluginConfig::DOMAIN_TRANSLATE); ?></label>
            </th>
            <td>
                <input type="checkbox" name="entity[enabled]" value="1" <?php echo $entityData['enabled'] ? 'checked' : ''; ?>>
            </td>
        </tr>
        <tr class="form-field">
            <th>
                <label><?php echo __('Nom de l\'entité', PluginConfig::DOMAIN_TRANSLATE);?></label>
            </th>
            <td>
                <input name="entity[name]" <?php isset($errorsData['name']) ? 'class="error"' : ''; ?>value="<?php echo($entityData['name']); ?>" >
                <?php if (isset($errorsData['name'])) { ?>
                    <label class="errorLabel alert-info" ><?php echo $errorsData['name']; ?>.</label>
                <?php } ?>
            </td>
        </tr>
        <tr class="form-field form-required">
            <th>
                <label><?php echo __('Identifiant de l\'entité', PluginConfig::DOMAIN_TRANSLATE); ?></label>
            </th>
            <td>
                <input name="entity[smEntityId]" <?php isset($errorsData['smEntityId']) ? 'class="error"' : ''; ?>value="<?php echo($entityData['smEntityId']); ?>" >
                <?php if (isset($errorsData['smEntityId'])) { ?>
                    <label class="errorLabel alert-info" ><?php echo $errorsData['smEntityId']; ?>.</label>
                <?php } ?>
            </td>
        </tr>
        <tr class="form-field form-required">
            <th>
                <label><?php echo __('Auteur', PluginConfig::DOMAIN_TRANSLATE);?></label>
            </th>
            <td>
                <select name="entity[authorId]">
                    <?php foreach ($authorList as $author) {?>
                        <option value="<?php echo $author['id']; ?>"<?php echo $author['id'] == $entityData['authorId'] ? 'selected' : ''; ?>><?php echo $author['displayName']; ?></option>
                    <?php }?>
                </select>
            </td>
        </tr>
        <tr class="form-field form-required">
            <th>
                <label><?php echo __('Ajout un \'En savoir plus\' à la fin de la publication', PluginConfig::DOMAIN_TRANSLATE); ?></label>
            </th>
            <td>
                <input name="entity[readMoreEnabled]" type="checkbox" value="1" <?php echo $entityData['readMoreEnabled'] ? 'checked' : ''; ?> >
            </td>
        </tr>
        <tr class="form-field form-required">
            <th>
                <label><?php echo __('Label du lien \'En savoir plus\'', PluginConfig::DOMAIN_TRANSLATE); ?></label>
            </th>
            <td>
                <input name="entity[readMoreLabel]" value="<?php echo sanitize_text_field($entityData['readMoreLabel']); ?>" placeholder="En savoir plus">
            </td>
        </tr>
        <tr class="form-field form-required">
            <th>
                <label><?php echo __('Afficher les articles dans une fenêtre modale', PluginConfig::DOMAIN_TRANSLATE); ?></label>
            </th>
            <td>
                <input type="checkbox" name="entity[displayInModal]" value="1" <?php echo $entityData['displayInModal'] ? 'checked' : ''; ?>>
            </td>
        </tr>
        <tr class="form-field form-required">
            <th>
                <label><?php echo __('Ne pas suivre les liens', PluginConfig::DOMAIN_TRANSLATE); ?></label>
            </th>
            <td>
                <input type="checkbox" name="entity[noFollow]" value="1" <?php echo $entityData['noFollow'] ? 'checked' : ''; ?>>
            </td>
        </tr>
        <tr class="form-field form-required">
            <th><?php echo __('Catégorie cible de la publication', PluginConfig::DOMAIN_TRANSLATE); ?></th>
<td>
    <fieldset>
        <?php foreach (get_categories(['hide_empty' => 0]) as $category) { ?>
        <label>
            <label style="margin-right: 8px !important;">
                <input name="entity[categories][]" type="checkbox"
                       value="<?php echo $category->cat_ID; ?>"
                    <?php echo in_array($category->cat_ID, $entityData['targetCategoriesId']) ? 'checked' : ''; ?>
                >
                <?php echo $category->name; ?>
            </label>
        </label>
        <?php } ?>
    </fieldset>

        </td>
        </tr>
        <tr class="form-field form-required">
            <th>
                <label><?php echo __('Image', PluginConfig::DOMAIN_TRANSLATE); ?></label>
            </th>
            <td>
                <select name="entity[imagePublicationType]">
                    <option value="content" <?php echo $entityData['imagePublicationType'] == 'content' ? ('selected') : ''; ?> ><?php echo __('Insérer dans le contenu', PluginConfig::DOMAIN_TRANSLATE);?></option>
                    <option value="thumbnail" <?php echo $entityData['imagePublicationType'] == 'thumbnail' ? ('selected') : ''; ?>><?php echo __('Insérer en tant qu\'image à la une', PluginConfig::DOMAIN_TRANSLATE); ?></option>
                    <option value="both" <?php echo $entityData['imagePublicationType'] == 'both' ? ('selected') : ''; ?>><?php echo __('Insérer en tant que contenu et image à la une', PluginConfig::DOMAIN_TRANSLATE); ?></option>
                </select>
            </td>
        </tr>
        <tr class="form-field form-required">
            <th>
                <label><?php echo __('Afficher le crédit des images', PluginConfig::DOMAIN_TRANSLATE); ?></label>
            </th>
            <td>
                <input type="checkbox" name="entity[creditImage]" value="1"
                    <?php
                    if (is_null($entityData['creditImage'])) {
                        echo 'checked';
                    } else {
                        echo $entityData['creditImage'] ? 'checked' : '';
                    }
                    ?>>
            </td>
        </tr>
        <tr class="form-field form-required">
            <th>
                <label><?php echo __('Inclure les balises de liens canoniques', PluginConfig::DOMAIN_TRANSLATE); ?></label>
            </th>
            <td>
            <input id="includeCanonicalLink" type="checkbox" name="entity[includeCanonicalLink]" value="1" onclick="verifySEO()" <?php echo $entityData['includeCanonicalLink'] ? 'checked' : ''; ?>>
            </td>
        </tr>

        <tr class="form-field form-required" id="compatibilityYoastSEO">
            <th>
                <label><?php echo __('Compatibilité avec YoastSEO', PluginConfig::DOMAIN_TRANSLATE); ?></label>
            </th>
            <td>
                <input type="checkbox" name="entity[compatibilityYoastSEO]" value="1" <?php echo $entityData['compatibilityYoastSEO'] ? 'checked' : ''; ?>>
            </td>
        </tr>

        <tr class="form-field form-required">
            <th>
                <label><?php echo __('Type de publication', PluginConfig::DOMAIN_TRANSLATE); ?></label>
            </th>
            <td>
                <select name="entity[publicationType]">
                    <option value="publish" <?php echo $entityData['publicationType'] == 'publish' ? ('selected') : ''; ?> ><?php echo __('Publié', PluginConfig::DOMAIN_TRANSLATE); ?></option>
                    <option value="draft" <?php echo $entityData['publicationType'] == 'draft' ? ('selected') : ''; ?>><?php echo __('Brouillon', PluginConfig::DOMAIN_TRANSLATE); ?></option>
                    <option value="pending" <?php echo $entityData['publicationType'] == 'pending' ? ('selected') : ''; ?>><?php echo __('En attente de relecture', PluginConfig::DOMAIN_TRANSLATE); ?></option>
                    <option value="private" <?php echo $entityData['publicationType'] == 'private' ? ('selected') : ''; ?>><?php echo __('Privé', PluginConfig::DOMAIN_TRANSLATE); ?></option>
                </select>
            </td>
        </tr>
    </tbody>
</table>

<p class="submit">
    <button type="submit" name="submit" id="submit" class="button button-primary">
    <?php echo __('Enregistrer', PluginConfig::DOMAIN_TRANSLATE); ?>
    </button>
</p>


<script>
    function verifySEO() {
        if (document.getElementById('includeCanonicalLink').checked) {
            document.getElementById('compatibilityYoastSEO').classList.remove('hidden');
        }else {
            document.getElementById('compatibilityYoastSEO').classList.add('hidden');
        }
    }

    verifySEO();

</script>
