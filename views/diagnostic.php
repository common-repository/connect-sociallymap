<?php

use SociallymapConnect\Configs\System\RequiredVersionConfig;
use SociallymapConnect\Configs\PluginConfig;

$requestInsecureMessage = '<p class="alert-info">' . __('Attention vos requêtes seront envoyées de manière non sécurisée', PluginConfig::DOMAIN_TRANSLATE) . '</p>';
$requestSecureMessage = '<p class="success-info">' . __('Requêtes sécurisées', PluginConfig::DOMAIN_TRANSLATE) . '</p>';

$messageRequest = $insecure ? $requestSecureMessage : $requestInsecureMessage;

$versionsRequired = [
    'CUrl'     => RequiredVersionConfig::CURL,
    'PHP'      => RequiredVersionConfig::PHP,
    'Wordpress' => RequiredVersionConfig::WORDPRESS,
    'PluginConfig'   => RequiredVersionConfig::PLUGIN,
];

$baseUrl = admin_url('admin.php?page=sociallymap-diagnostic');

?>

<div class="wrap">
    <h1>Sociallymap Connect</h1>
    <p>
        <?php echo __('Bienvenue dans Sociallymap Connect', PluginConfig::DOMAIN_TRANSLATE) ?>.
    </p>
    <div>
        <div class="serverInfos">
            <h4><?php echo __('Etat du support du plugin :', PluginConfig::DOMAIN_TRANSLATE); ?></h4>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <td></td>
                        <td><?php echo __('requis', PluginConfig::DOMAIN_TRANSLATE); ?></td>
                        <td><?php echo __('actuel', PluginConfig::DOMAIN_TRANSLATE); ?></td>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($serverInfos as $key => $value) {
                        echo '<tr>';
                        echo '<td class="version-label">' . $key . '</td>';
                        echo '<td class="version-label">' . $versionsRequired[$key] . '</td>';
                        echo '<td class="version-status-' . (($value['status']) ? 'ok' : 'not-supported') . '">';
                        echo $value['version'];
                        echo '</td>';
                        echo '</tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
        <br>
        <div>
            <h4><?php echo __('Sécurisation des requêtes', PluginConfig::DOMAIN_TRANSLATE); ?></h4>
            <form action="?page=sociallymap-diagnostic" method="POST">
                <label for="insecure"><?php echo __('Requête sécurisée', PluginConfig::DOMAIN_TRANSLATE); ?></label>
                <input type="checkbox" <?php echo $insecure ? 'checked' : '' ?>
                       id="checkbox[secureRequest]"> <?php echo $messageRequest; ?>
                <input type="hidden" name="insecure" id="hidden">
            </form>
        </div>
        <br>
        <div>
            <h4><?php echo __('Configuration', PluginConfig::DOMAIN_TRANSLATE); ?></h4>
            <table>
                <?php
                foreach ($phpInfos as $key => $value) {
                    echo '<tr>';
                    echo '<td class="version-label">' . $key . '</td>';
                    echo '<td class="version-status-' . $value['status'] . '">';
                    echo $value['present'];
                    echo '</td>';
                    echo '</tr>';
                }
                ?>
            </table>
        </div>
        <br>
        <button type="button" class="download-logs">
            <a href="<?php echo $baseUrl . "&export_all_posts" ?>"><?php echo __('Téléchargement des logs', PluginConfig::DOMAIN_TRANSLATE); ?></a>
        </button>
    </div>
</div>


<script>
    document.getElementById('checkbox[secureRequest]').onchange = checkSecureRequest;

    function checkSecureRequest() {
        var hidden = document.getElementById('hidden');
        var checkbox = document.getElementById('checkbox[secureRequest]');
        hidden.value = checkbox.checked;
        checkbox.form.submit();
    }
</script>
