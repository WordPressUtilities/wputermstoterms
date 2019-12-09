<?php

/*
Plugin Name: WPU Terms to Terms
Plugin URI: https://github.com/WordPressUtilities/wputermstoterms
Description: Link terms to terms from the term edit page.
Version: 0.1.1
Author: Darklg
Author URI: http://darklg.me/
License: MIT License
License URI: http://opensource.org/licenses/MIT
*/

class WPUTermsToTerms {
    public function __construct() {
        add_filter('plugins_loaded', array(&$this, 'plugins_loaded'));
        add_action('edit_term', array(&$this, 'edit_term'), 60, 3);
    }

    public function plugins_loaded() {
        $this->linked_terms = $this->get_linked_terms();
    }

    /* ----------------------------------------------------------
      Main
    ---------------------------------------------------------- */

    public function edit_term($term_id, $tt_id, $taxonomy) {

        /* Lets check if a term is concerned */
        foreach ($this->linked_terms as $k => $_linkedterm) {
            if ($taxonomy != $_linkedterm['from']['taxonomy']) {
                continue;
            }
            $this->update_term_links($term_id, $k, $_linkedterm);
        }

    }

    public function update_term_links($term_id, $k, $linkedterm) {

        /* Get previous linked terms */
        $linkedterms_before = get_term_meta($term_id, $k, 1);
        if (!is_array($linkedterms_before)) {
            $linkedterms_before = array();
        }

        /* Get current linked terms */
        $linkedterms_now = get_term_meta($term_id, $linkedterm['from']['meta_key'], 1);
        if (!is_array($linkedterms_now)) {
            $linkedterms_now = array();
        }
        $linkedterms_now = array_map('intval', $linkedterms_now);

        /* Find removed terms */
        $removedlinks = array();
        foreach ($linkedterms_before as $removed_term) {
            if (!in_array($removed_term, $linkedterms_now)) {
                $removedlinks[] = intval($removed_term, 10);
            }
        }

        /* Remove all old links */
        foreach ($removedlinks as $target_term_id) {
            $this->change_link('remove', $target_term_id, $term_id, $linkedterm['to']['meta_key'], $k);
        }

        /* Add new links */
        foreach ($linkedterms_now as $target_term_id) {
            $this->change_link('add', $target_term_id, $term_id, $linkedterm['to']['meta_key'], $k);
        }

        /* Save linked terms */
        update_term_meta($term_id, $k, $linkedterms_now);
    }

    public function change_link($type = 'remove', $target_term_id, $term_id, $meta_key, $meta_key_before) {
        if ($target_term_id == $term_id) {
            return;
        }

        /* Get links for the target */
        $target_links = get_term_meta($target_term_id, $meta_key, 1);
        if (!is_array($target_links)) {
            $target_links = array();
        }

        if ($type == 'remove') {
            $pos = array_search($term_id, $target_links);
            if (isset($target_links[$pos])) {
                unset($target_links[$pos]);
            }
        } else {
            if (!in_array($term_id, $target_links)) {
                $target_links[] = intval($term_id, 10);
            }
        }

        /* Save term */
        update_term_meta($target_term_id, $meta_key, $target_links);
        update_term_meta($target_term_id, $meta_key_before, $target_links);

    }

    /* ----------------------------------------------------------
      Settings
    ---------------------------------------------------------- */

    public function get_linked_terms() {
        $_linkedterms = array();
        $linkedterms = apply_filters('wputermstoterms_linkedterms', array());
        foreach ($linkedterms as $k => $linkedterm) {
            if (!is_array($linkedterm) || !isset($linkedterm['from'], $linkedterm['to'], $linkedterm['from']['taxonomy'], $linkedterm['to']['taxonomy'], $linkedterm['from']['meta_key'], $linkedterm['from']['meta_key'])) {
                continue;
            }
            $_linkedterms[$k] = $linkedterm;
        }
        return $_linkedterms;
    }
}

$WPUTermsToTerms = new WPUTermsToTerms();
