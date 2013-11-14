<ul class="indexer-search-results">
    <?php
    foreach($results as $result){
    ?>
        <li>
            <a href="<?php echo $result['page']->getUrl(); ?>"><?php echo $result['page']->getTitle(); ?></a>
            <?php
            foreach($result['highlights']['content'] as $highlight){
            ?>
                <div class="highlighted-search-results"> <?php echo $highlight; ?> </div>
            <?php }
            ?>
            <div class="search-results-score"><?php echo $result['score']; ?></div>
        </li>
    <?php }
    ?>
</ul>