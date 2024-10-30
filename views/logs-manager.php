<?php

use SociallymapConnect\Configs\PluginConfig;

$currentPage = array_key_exists('currentPage', $_REQUEST) ? $_REQUEST['currentPage'] : 1;
$currentFilterUrl = isset($_REQUEST['filter']) ? '&filter=' . $_REQUEST['filter'] : '';
$nbPage = ceil($totalCount / $maxPerPage);
$baseUrl = admin_url('admin.php?page=sociallymap-logsManager');
$logsDonwloadUrl = admin_url('admin.php?page=sociallymap-logsDownload');
$paginationData = [
    'page'    => $currentPage,
    'nbPage'  => $nbPage,
    'baseUrl' => $baseUrl,
    'filter'  => $currentFilterUrl
];
?>
<div class="wrap">
    <h1><?php echo __('Logs', PluginConfig::DOMAIN_TRANSLATE); ?></h1>

    <button type="button" class="download-logs">
        <a href="<?php echo $baseUrl . "&export_all_posts" ?>"><?php echo __('Téléchargement', PluginConfig::DOMAIN_TRANSLATE); ?></a>
    </button>


    <div class="sociallymap-log-container">
        <h2 class="screen-reader-text"><?php echo __('Filtrer les logs', PluginConfig::DOMAIN_TRANSLATE); ?></h2>
        <ul class="subsubsub">
            <li class="filter-all"><a href="<?php echo $baseUrl; ?>" class="current"
                                      aria-current="page"><?php echo __('Tous'); ?><span
                            class="count">(<?php echo $totalCount; ?>)</span></a> |
            </li>
            <li class="filter-error"><a href="<?php echo $baseUrl; ?>&filter=error"><?php echo __('Erreur', PluginConfig::DOMAIN_TRANSLATE); ?><span
                            class="count">(<?php echo $errorsCount; ?>)</span></a></li>
            <li class="filter-infos"><a href="<?php echo $baseUrl; ?>&filter=info"><?php echo __('Infos', PluginConfig::DOMAIN_TRANSLATE); ?><span
                            class="count">(<?php echo $infosCount; ?>)</span></a></li>
            <li class="filter-messages"><a
                        href="<?php echo $baseUrl; ?>&filter=message_received"><?php echo __('Messages reçus', PluginConfig::DOMAIN_TRANSLATE); ?><span
                            class="count">(<?php echo $messagesCount; ?>)</span></a></li>
        </ul>
        <form method="post">
            <div class="tablenav top">
                <div class="tablenav-pages<?php echo $nbPage === 1 ? ' one-page' : ''; ?>">
                    <span class="displaying-num"><?php echo sprintf(__('%d element(s)'), $totalCount); ?></span>
                    <?php $this->includeView('partial/pagination', $paginationData); ?>
                </div>
            </div>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                <tr>
                    <th id="logDate" class="manage-column col-date">
                        <?php echo __('Date', PluginConfig::DOMAIN_TRANSLATE); ?>
                    </th>
                    <th id="category" class="manage-column col-category">
                        <?php echo __('Catégorie', PluginConfig::DOMAIN_TRANSLATE); ?>
                    </th>
                    <th class="manage-column col-message">
                        <?php echo __('Message', PluginConfig::DOMAIN_TRANSLATE); ?>
                    </th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($logsList as $log) { ?>
                    <tr>
                        <td><?php echo $log['log_date']; ?></td>
                        <td><?php echo $log['log_level']; ?></td>
                        <td><?php echo $log['log_message']; ?></td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
            <div class="tablenav bottom">
                <div class="tablenav-pages<?php echo $nbPage == 1 ? ' one-page' : ''; ?>">

                    <span class="displaying-num"><?php echo sprintf(__('%d element(s)'), $totalCount); ?></span>
                    <?php
                    $paginationData['noField'] = true;
                    $this->includeView('partial/pagination', $paginationData);
                    ?>
                </div>
            </div>
        </form>
    </div>
</div>
