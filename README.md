# Cleanify

> Requires PHP 5.4

A simple plugin to remove slug from custom post types.

# Filters

#### cleanify/cpts

Return all post types where the slug should be removed.
Should be a string or array of strings.

# Usage

```php
add_filter('cleanify/cpts', function () {
  return 'post-type';
});
```

## License

MIT © [Fredrik Forsmo](https://github.com/frozzare)
