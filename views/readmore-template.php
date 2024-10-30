<?php
$data = get_query_var('data');
$url = $data['url'];
$display_type = $data['display_type'];
$entityId = $data['entityId'];
$readmore = $data['readmore'];

if ($readmore === '') {
    $readmore = 'Lire la suite';
}

?>

<!--more-->
<p><a class="sm-readmore-link sm-display-modal" data-entity-id="<?php echo($entityId); ?>" href="<?php echo($url); ?>"
      data-article-url="<?php echo($url); ?>" target="_blank"><?php echo $readmore; ?></a><p>


