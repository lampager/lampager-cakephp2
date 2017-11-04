<p align="center">
<img width="320" alt="lampager-cakephp2" src="https://user-images.githubusercontent.com/1351893/32145370-967f8572-bd0a-11e7-8324-10854958fd7f.png">
</p>
<p align="center">
<a href="https://travis-ci.org/lampager/lampager-cakephp2"><img src="https://travis-ci.org/lampager/lampager-cakephp2.svg?branch=master" alt="Build Status"></a>
<a href="https://coveralls.io/github/lampager/lampager-cakephp2?branch=master"><img src="https://coveralls.io/repos/github/lampager/lampager-cakephp2/badge.svg?branch=master" alt="Coverage Status"></a>
<a href="https://scrutinizer-ci.com/g/lampager/lampager-cakephp2/?branch=master"><img src="https://scrutinizer-ci.com/g/lampager/lampager-cakephp2/badges/quality-score.png?b=master" alt="Scrutinizer Code Quality"></a>
</p>

# Lampager for CakePHP 2

Rapid pagination without using OFFSET

## Requirements

- PHP: ^5.6 || ^7.0
- CakePHP: ^2.10
- [lampager/lampager](https://github.com/lampager/lampager): ^0.2

## Basic Usage

Load as a plugin. See [How To Install Plugins - 2.x](https://book.cakephp.org/2.0/en/plugins/how-to-install-plugins.html) for detail.

Plugin needs to be loaded manually in `app/Config/bootstrap.php`:

```php
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

### Use in Controller

At first, your `Model` class must have `'Lampager.Lampager'` enabled.

Accept cursor parameters from a request and pass it through `PaginatorComponent::settings`.

```php
class PostsController extends AppController
{
    // Load default PaginatorComponent of CakePHP
    public $components = [
        'Paginator',
    ];

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
            'limit' => 15,
        ];

        /** @var mixed[][] */
        $posts = $this->Paginator->paginate(Post::class);
        $this->set('posts', $posts);
    }
}
```

And the pagination links can be output as follows:

```php
// If the previous_cursor exists, there is a previous page
if (isset($posts['meta']['previous_cursor'])) {
    echo $this->Html->link('<< Previous', [
        'controller' => 'posts',
        'action' => 'index',
        'previous_cursor' => $posts['meta']['previous_cursor'],
    ]);
}

// If the next_cursor exists, there is a next page
if (isset($posts['meta']['next_cursor'])) {
    echo $this->Html->link('Next >>', [
        'controller' => 'posts',
        'action' => 'index',
        'next_cursor' => $posts['meta']['next_cursor'],
    ]);
}
```

### Use in Model

At first, your `Model` class must have `'Lampager.Lampager'` enabled.

Simply use `Model::find` with `lampager`.

```php
class Post extends AppModel
{
    public function latest(array $cursor = [])
    {
        return $this->find('lampager', [
            // Lampager options
            'forward' => true,
            'seekable' => true,
            'cursor' => $cursor,

            // Model::find query
            'limit' => 10,
            'order' => [
                'Post.modified' => 'DESC',
                'Post.created' => 'DESC',
                'Post.id' => 'DESC',
            ],
        ]);
    }
}
```

## Classes

See also: [lampager/lampager](https://github.com/lampager/lampager).

| Name                     | Type  | Extends                       | Description                                                                         |
|:-------------------------|:------|:------------------------------|:------------------------------------------------------------------------------------|
| `LampagerBehavior`       | Class | ModelBehavior                 | CakePHP behavior which handles `Model::find()` and `PaginatorComponent::paginate()` |
| `LampagerArrayCursor`    | Class | Lampager\\Contracts\\`Cursor` | Multi-dimensional array cursor                                                      |
| `LampagerColumnAccess`   | Class |                               | Multi-dimensional array accessor                                                    |
| `LampagerTransformer`    | Class |                               | CakePHP query genenrator                                                            |
| `LampagerArrayProcessor` | Class | Lampager\\`ArrayProcessor`    | Processor implementation for CakePHP                                                |
| `LampagerPaginator`      | Class | Lampager\\`Paginator`         | Paginator implementation for CakePHP                                                |

## API

See also: [lampager/lampager](https://github.com/lampager/lampager).

Using `Model::find()` or `PaginatorComponent::paginate()` is recommended. The
query is merged with CakePHP query and passed to `Lampager\Query`.
