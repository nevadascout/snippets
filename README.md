# snippets
An easy way to share and explain code snippets

## Todo list

- [ ] Log in with Github
- [ ] Create snippet
- [ ] View snippet
- [ ] Add comments to snippet


## Dev Notes

* Access variables from `.env` file using `$value = $_ENV["NAME"];`

```
$router->get('/(\d+)', '\App\Controllers\User@showProfile');

$router->set404('\App\Controllers\Error@notFound');
```

To install composer dependencies:
* Download `composer.phar` from getcomposer.org and place inside the `api/` directory
* Run `php composer.phar install` from a terminal window inside the `api/` directory to install dependencies
