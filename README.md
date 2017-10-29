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

Now you are done. Use `Model::find` with `lampager`.

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
