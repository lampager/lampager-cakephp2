<p align="center">
<img width="320" alt="lampager-cakephp2" src="https://user-images.githubusercontent.com/1351893/32145370-967f8572-bd0a-11e7-8324-10854958fd7f.png">
</p>
<p align="center">
<a href="https://travis-ci.com/lampager/lampager-cakephp2"><img src="https://travis-ci.com/lampager/lampager-cakephp2.svg?branch=master" alt="Build Status"></a>
<a href="https://coveralls.io/github/lampager/lampager-cakephp2?branch=master"><img src="https://coveralls.io/repos/github/lampager/lampager-cakephp2/badge.svg?branch=master" alt="Coverage Status"></a>
<a href="https://scrutinizer-ci.com/g/lampager/lampager-cakephp2/?branch=master"><img src="https://scrutinizer-ci.com/g/lampager/lampager-cakephp2/badges/quality-score.png?b=master" alt="Scrutinizer Code Quality"></a>
</p>

# Lampager for CakePHP 2

Rapid pagination without using OFFSET

## Requirements

- PHP: ^5.6 || ^7.0
- CakePHP: ^2.10
- [lampager/lampager][]: ^0.4

Note: [lampager/lampager-cakephp][] for CakePHP 3.x is available!

## Installing

```bash
composer require lampager/lampager-cakephp2
```

Move `Plugin/Lampager` to the appropriate directory if necessary.

## Basic Usage

Load as a plugin. See [How To Install Plugins][] for detail.

Plugin needs to be loaded manually in `app/Config/bootstrap.php`:

```php
// Be sure to require vendor/autoload.php beforehand.
// CakePlugin::load() will fail unless autoloader is properly configured.
CakePlugin::load('Lampager');
```

Next, add `'Lampager.Lampager'` to your Model class (`AppModel` is preferable):

```php
class AppModel extends Model
{
    public $actsAs = [
        'Lampager.Lampager',
    ];
}
```

Use in one or more of the following methods:

- Use in Controller (via `LampagerBehavior`)
- Use in Model (via `LampagerBehavior`)

### Use in Controller

At first, your `Model` class must have `'Lampager.Lampager'` enabled. Use in a
way described in the Cookbook: [Pagination][]. Note the options that are
specific to Lampager such as `forward`, `seekable`, or `cursor`.

```php
$posts = $this->paginate(Post::class, [
    // Lampager options
    'forward' => true,
    'seekable' => true,
    'cursor' => [
        'Post' => [
            'id' => '4',
            'created' => '2017-01-01 10:00:00',
        ],
    ],

    // PaginatorComponent::settings query
    'conditions' => [
        'Post.type' => 'public',
    ],
    'order' => [
        'Post.created' => 'DESC',
        'Post.id' => 'DESC',
    ],
    'limit' => 10,
]);

$this->set('posts', $posts);
```

### Use in Model

At first, your `Model` class must have `'Lampager.Lampager'` enabled. Simply use
`Model::find` with `lampager`. The custom find type `lampager` (see
[Retrieving Your Data][]) works in a way similar to the core find type `all`
with additional parameters and post processor enabled.

```php
/** @var \Lampager\PaginationResult $posts */
$posts = $this->find('lampager', [
    // Lampager options
    'forward' => true,
    'seekable' => true,
    'cursor' => [
        'Post' => [
            'id' => '4',
            'created' => '2017-01-01 10:00:00',
        ],
    ],

    // Model::find query
    'limit' => 10,
    'order' => [
        'Post.modified' => 'DESC',
        'Post.created' => 'DESC',
        'Post.id' => 'DESC',
    ],
]);

foreach ($posts as $post) {
    /** @var mixed[][] $post */
    debug($post['Post']['id']);
    debug($post['Post']['created']);
    debug($post['Post']['modified']);
}
```

## Classes

See also: [lampager/lampager][].

| Name                     | Type  | Extends                       | Description                                                                         |
|:-------------------------|:------|:------------------------------|:------------------------------------------------------------------------------------|
| `LampagerBehavior`       | Class | ModelBehavior                 | CakePHP behavior which handles `Model::find()` and `PaginatorComponent::paginate()` |
| `LampagerArrayCursor`    | Class | Lampager\\Contracts\\`Cursor` | Multi-dimensional array cursor                                                      |
| `LampagerPaginator`      | Class | Lampager\\`Paginator`         | Paginator implementation for CakePHP                                                |
| `LampagerArrayProcessor` | Class | Lampager\\`ArrayProcessor`    | Processor implementation for CakePHP                                                |
| `LampagerColumnAccess`   | Class |                               | Multi-dimensional array accessor                                                    |
| `LampagerTransformer`    | Class |                               | CakePHP query genenrator                                                            |

## API

See also: [lampager/lampager][].

Using `Model::find()` or `PaginatorComponent::paginate()` is recommended. The
query is merged with CakePHP query and passed to `Lampager\Query`.

### LampagerPaginator::\_\_construct()<br>LampagerPaginator::create()

Create a new paginator instance. These methods are not intended to be directly
used in your code.

```php
static LampagerPaginator::create(Model $builder, array $options): static
LampagerPaginator::__construct(Model $builder, array $options)
```

### LampagerPaginator::transform()

Transform a Lampager query into a CakePHP query.

```php
LampagerPaginator::transform(\Lampager\Query $query): array
```

### LampagerPaginator::build()

Perform configure + transform.

```php
LampagerPaginator::build(array $cursor = []): array
```

### LampagerPaginator::paginate()

Perform configure + transform + process.

```php
LampagerPaginator::paginate(array $cursor = []): \Lampager\PaginationResult
```

#### Arguments

- **`(array)`** __*$cursor*__<br> An associative array that contains `$column => $value`. It must be **all-or-nothing**.
  - For the initial page, omit this parameter or pass an empty array.
  - For the subsequent pages, pass all the parameters. The partial one is not allowed.

#### Return Value

e.g.,

(Default format when using `Model::find()`)

```php
object(Lampager\PaginationResult)#1 (5) {
  ["records"]=>
  array(3) {
    [0]=>
    array(1) {
      ["Post"]=>
      array(3) { ... }
    }
    [1]=>
    array(1) {
      ["Post"]=>
      array(3) { ... }
    }
    [2]=>
    array(1) {
      ["Post"]=>
      array(3) { ... }
    }
  }
  ["hasPrevious"]=>
  bool(false)
  ["previousCursor"]=>
  NULL
  ["hasNext"]=>
  bool(true)
  ["nextCursor"]=>
  array(1) {
    ["Post"]=>
    array(2) {
      ["id"]=>
      string(1) "3"
      ["created"]=>
      string(19) "2017-01-01 10:00:00"
    }
  }
}
```

### LampagerTransformer::\_\_construct()

Create a new transformer instance. This class is not intended to be directly
used in your code.

```php
LampagerTransformer::__construct(Model $builder, array $options)
```

## Examples

This section describes the practial usages of lampager-cakephp2.

### Use in Controller

The example below shows how to accept a cursor parameter from a request and
pass it through `PaginatorComponent::settings`. Be sure that your `Model` class
has `'Lampager.Lampager'` enabled.

```php
class PostsController extends AppController
{
    public function index()
    {
        // Get cursor parameters
        $previous = $this->request->param('named.previous_cursor');
        $next = $this->request->param('named.next_cursor');

        $this->Paginator->settings = [
            // Lampager options
            // If the previous_cursor is not set, paginate forward; otherwise backward
            'forward' => !$previous,
            'cursor' => $previous ?: $next ?: [],
            'seekable' => true,

            // PaginatorComponent::settings query
            'conditions' => [
                'Post.type' => 'public',
            ],
            'order' => [
                'Post.created' => 'DESC',
                'Post.id' => 'DESC',
            ],
            'limit' => 10,
        ];

        /** @var mixed[][] */
        $posts = $this->Paginator->paginate(Post::class);
        $this->set('posts', $posts);
    }
}
```

And the pagination links can be output as follows:

```php
// If there is a previous page, print pagination link
if ($posts->hasPrevious) {
    echo $this->Html->link('<< Previous', [
        'controller' => 'posts',
        'action' => 'index',
        'previous_cursor' => $posts->previousCursor,
    ]);
}

// If there is a next page, print pagination link
if ($posts->hasNext) {
    echo $this->Html->link('Next >>', [
        'controller' => 'posts',
        'action' => 'index',
        'next_cursor' => $posts->nextCursor,
    ]);
}
```

## Supported database engines

### MySQL, MariaDB, PostgreSQL, and SQLite

Supported!

### Microsoft SQL Server

Not supported.

[lampager/lampager]:         https://github.com/lampager/lampager
[lampager/lampager-cakephp]: https://github.com/lampager/lampager-cakephp
[How To Install Plugins]:    https://book.cakephp.org/2/en/plugins/how-to-install-plugins.html
[Pagination]:                https://book.cakephp.org/2/en/core-libraries/components/pagination.html
[Retrieving Your Data]:      https://book.cakephp.org/2/en/models/retrieving-your-data.html#creating-custom-find-types
