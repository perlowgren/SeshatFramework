<?php

use Seshat\WadjetWiki;

$wiki = new WadjetWiki('{*~wiki-index}');
$wiki->handleAction();

?>
{* include theme/{*~THEME}/page-header.php }
<article class="wiki">

<!-- Wiki page: -->
<?= $wiki->getHTML() ?>
<!-- End of Wiki page -->

</article>
{* include theme/{*~THEME}/page-footer.php }
