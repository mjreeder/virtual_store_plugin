<?php
add_filter("the_content", "dcvs_landing_page_content");

function dcvs_landing_page_content($content){
    if(is_page("virtual-store-landing")){
        ob_start();
        include  __DIR__ . "/templates/landing.php";

        $pluginContent = ob_get_contents();
        ob_end_clean();
        return $pluginContent;

    }
    return $content;
}
