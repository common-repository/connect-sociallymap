<?php

use SociallymapConnect\Configs\PluginConfig;

$label = (!empty($label)) ? $label : __('En savoir plus', PluginConfig::DOMAIN_TRANSLATE);
?>
<!--more-->
<p><a class="sm-readmore-link<?php echo $displayInModal ? ' sm-display-modal' : '' ?>" data-entity-id="<?php echo $entityId; ?>" href="<?php echo $url; ?>" data-article-url="<?php echo $url; ?>" target="_blank" <?php echo $noFollow ? 'rel="nofollow"' : null; ?>><?php echo $label; ?></a></p>
