<span class="pagination-links">
	<?php

    use SociallymapConnect\Configs\PluginConfig;
    use SociallymapConnect\Includes\Enum;

    if ($page > 2 && $nbPage > 1) {
        ?>
        <a class="first-page" href="<?php echo $baseUrl . $filter . '&currentPage=1'; ?>"><span
                    class="screen-reader-text"><?php echo __('Première page', PluginConfig::DOMAIN_TRANSLATE); ?></span><span
                    aria-hidden="true">«</span></a>
        <?php
    } else {
        ?>
        <span class="tablenav-pages-navspan" aria-hidden="true">«</span>
        <?php
    }
    if ($page > 1 && $nbPage > 1) {
        ?>
        <a class="prev-page" href="<?php echo $baseUrl . $filter . '&currentPage=' . ($page - 1); ?>"><span
                    class="screen-reader-text"><?php echo __('Page précédente', PluginConfig::DOMAIN_TRANSLATE); ?></span><span
                    aria-hidden="true">‹</span></a>
        <?php
    } else {
        ?>
        <span class="tablenav-pages-navspan" aria-hidden="true">‹</span>
        <?php
    }
    ?>
    <span class="paging-input">
		<label for="current-page-selector" class="screen-reader-text"><?php echo __('Page actuelle'); ?></label>
        <?php
        if (isset($noField)) {
            echo $page;
        } else {
            ?>
            <input class="current-page" id="current-page-selector" type="text" name="currentPage"
                   value="<?php echo $page; ?>" size="1" aria-describedby="table-paging">
            <?php
        }
        ?>
        <span class="tablenav-paging-text"> <?php echo __('sur'); ?> <span
                    class="total-pages"><?php echo $nbPage; ?></span></span>
	</span>
    <?php
    if ($page < $nbPage && $nbPage > 1) {
        ?>
        <a class="next-page" href="<?php echo $baseUrl . $filter . '&currentPage=' . ($page + 1); ?>"><span
                    class="screen-reader-text"><?php echo __('Page suivante', PluginConfig::DOMAIN_TRANSLATE); ?></span><span
                    aria-hidden="true">›</span></a>
        <?php
    } else {
        ?>
        <span class="tablenav-pages-navspan" aria-hidden="true">›</span>
        <?php
    }
    if ($page < ($nbPage - 1) && $nbPage > 1) {
        ?>
        <a class="last-page" href="<?php echo $baseUrl . $filter . '&currentPage=' . $nbPage; ?>"><span
                    class="screen-reader-text"><?php echo __('Dernière page', PluginConfig::DOMAIN_TRANSLATE); ?></span><span
                    aria-hidden="true">»</span></a>
        <?php
    } else {
        ?>
        <span class="tablenav-pages-navspan" aria-hidden="true">»</span>
        <?php
    } ?>
</span>
