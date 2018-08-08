# WP Meta Box Colors

WordPress meta box plugin for adding page colors

## Usage

Register post type support

```
add_post_type_support( 'page', 'colors' )
```

Add a color for all supported post types

```
add_filter('wp-meta-box-colors-fields', function($colors){
	$colors[$key] = 'Label';
	return $colors;
});
```

Add a color for a specific post type

```
add_filter("wp-meta-box-colors-fields-$post_type", function($colors){
	$colors[$key] = 'Label';
	return $colors;
});
```
