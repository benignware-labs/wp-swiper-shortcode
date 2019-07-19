# Swiper shortcode

Swiper integration for Wordpress



## API Reference

### Functions

`swiper_shortcode($params, $content)`


`swiper_slide_shortcode($params, $content)`


`swiper_gallery_shortcode($params, $content)`


`get_swiper($template, $format, $params)`


`register_swiper_theme($name, $theme)`


## Development


Download [Docker CE](https://www.docker.com/get-docker) for your OS.
Download [NodeJS](https://nodejs.org) for your OS.

### Install

#### Install wordpress

```cli
docker-compose run --rm wp wp-install
```

After installation you can log in with user `wordpress` and password `wordpress`.

If you like, you can import Wordpress Standard Demo Content like this:

```cli
docker-compose run wp import vendor/wptrt/theme-unit-test --authors=skip
```

#### Install front-end dependencies

```cli
npm i
```

### Development Server

Point terminal to your project root and start up the container.

```cli
docker-compose up -d
```

Point your browser to [http://localhost:8080](http://localhost:8080).


#### Watch front-end dependencies

```cli
npm run watch
```

### Docker

##### Update composer dependencies

```cli
docker-compose run composer update
```

##### Globally stop all running docker containers

```cli
docker stop $(docker ps -a -q)
```


##### Update Wordpress

Due to some permission issues, you need to chmod your container's web-root prior to running the updater:

```cli
docker-compose exec wordpress bash
```

From the container shell, change permissons all down the tree.
```cli
chmod -R 777 .
```

After `CTRL+D`, you're ready to update Wordpress, either from the admin-interface or using wp-cli:

```cli
docker-compose run wp core update
```

## Production

Create a build for production

```cli
npm run build
```
