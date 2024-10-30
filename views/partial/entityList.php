
<div class="sociallymap-entity-list-container">
    <table class="wp-list-table widefat fixed striped">
        <form method="post">
            <input type="hidden" name="sociallymap_deleteEntity" value="1"/>
            <thead>
                <tr>
                    <th id="Entity" class="manage-column col-entity-name">
                        <?php use SociallymapConnect\Configs\PluginConfig;
                        use SociallymapConnect\Includes\Enum;

                        echo __('Nom de l\'entité', PluginConfig::DOMAIN_TRANSLATE); ?>
                    </th>

                    <th id="smEntityId" class="manage-column col-sm-entity-id">
                    <?php echo __('Identifiant de l\'entité sociallymap', PluginConfig::DOMAIN_TRANSLATE); ?>
                    </th>

                    <th id="category" class="manage-column col-category">
                    <?php echo __('Catégorie', PluginConfig::DOMAIN_TRANSLATE); ?>
                    </th>

                    <th id="author" class="manage-column col-author">
                    <?php echo __('Auteur', PluginConfig::DOMAIN_TRANSLATE); ?>
                    </th>

                    <th id="action" class="manage-column col-enabled">
                    <?php echo __('Active', PluginConfig::DOMAIN_TRANSLATE); ?>
                    </th>

                    <th id="last_publication" class="manage-column col-last-publication">
                    <?php echo __('Dernière publication', PluginConfig::DOMAIN_TRANSLATE); ?>
                    </th>

                    <th id="onError" class="manage-column col-errors">
                        <?php echo __('Erreurs', PluginConfig::DOMAIN_TRANSLATE); ?>
                    </th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach($listData as $entity)
                {
                    $entityOnError = ($entity->getErrorCounter() >= 3) ? true : false;

                ?>

                    <tr<?php echo $entityOnError == true ? ' class="on-warning"' : ''; ?>>
                        <td>
                            <?php echo $entity->getName(); ?>
                            <div class="row-actions">
                                <span class="edit">
                                    <a href="?page=sociallymap-entity-edit&id=<?php echo $entity->getId(); ?>"><?php echo __('Editer', PluginConfig::DOMAIN_TRANSLATE); ?></a>
                                </span>
                                <span class="delete">
                                    <a href="?page=sociallymap-entity-delete&id=<?php echo $entity->getId(); ?>" onclick="return confirm('<?php echo __('Voulez vous vraiment supprimer cette entité ?'); ?>');"><?php echo __('Effacer', PluginConfig::DOMAIN_TRANSLATE); ?></a>
                                </span>
                            </div>
                        </td>
                        <td>
                            <?php echo $entity->getSmEntityId(); ?>
                        </td>
                        <td>
                            <?php
                                $listCat = [];
                                foreach ($entity->getTargetCategoriesId() as $categoryId) {
                                    $listCat[] = get_the_category_by_ID($categoryId);
                            ?>
                            <?php }
                            echo join(', ',$listCat);
                            ?>
                        </td>
                        <td>
                            <?php echo (get_user_by('id', $entity->getAuthorId())->user_nicename); ?>
                        </td>
                        <td>
                            <input type="checkbox" class="disabled-cursor" disabled <?php if($entity->getEnabled() == true) echo "checked"; ?> >
                        </td>
                        <td>
                            <?php
                                $lastPublishedDate = $entity->getLastPublishedMessage();
                                if (!empty($lastPublishedDate)) {
                                    echo date_i18n(get_option('date_format'), strtotime($lastPublishedDate->format('m/d/Y')));
                                } else {
                                    echo __('Aucune','sociallymap-connect');
                                }
                            ?>
                        </td>
                        <td>
                            <?php
                            echo $entity->getErrorCounter();
                            ?>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </form>
    </table>
</div>
