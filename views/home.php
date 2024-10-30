<div class="wrap">
    <h1>Sociallymap Connect</h1>
    <p>
        <?php use SociallymapConnect\Configs\PluginConfig;

        echo __('Bienvenue dans Sociallymap Connect', PluginConfig::DOMAIN_TRANSLATE);?>.
    </p>
    <h1>
        <a href="?page=sociallymap-entity-add" class="page-title-action">
            <?php echo __('Ajouter une entité', PluginConfig::DOMAIN_TRANSLATE);?>
        </a>
    </h1>

    <div>
        <?php $this->includeView('partial/entityList', $listData);?>
    </div>
</div>
