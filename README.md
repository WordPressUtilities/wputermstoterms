# WPU Terms to Terms

Link terms to terms. Requires to create a multi-select taxonomy ACF field on each taxonomy item.
This plugin will update the linked list on the other side.

## How to install

```php
add_filter('wputermstoterms_linkedterms', 'examplenamespace_wputermstoterms_linkedterms', 10, 1);
function examplenamespace_wputermstoterms_linkedterms($terms_to_terms = array()) {
    $terms_to_terms['tag_to_category'] = array(
        'from' => array(
            'taxonomy' => 'post_tag',
            'meta_key' => 'linked_categories'
        ),
        'to' => array(
            'taxonomy' => 'category',
            'meta_key' => 'linked_tags'
        )
    );
    return $terms_to_terms;
}
```

## Todo

- [ ] Option to add a multiselect.
