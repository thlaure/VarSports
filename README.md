# VarSports

## Context
This project is a redesign of the original WordPress website.
VarSports aims to highlight sports clubs in the Var region of France.

## Technologies
### This redesign is built with:
- [Symfony 6.4](https://symfony.com/)
- [PHP 8.3](https://www.php.net/releases/8.3/en.php)
- [MariaDB](https://mariadb.org/)
- [Docker](https://www.docker.com/) / [FrankenPHP](https://frankenphp.dev/fr/), only for the development environment
- [Bootstrap 5.3](https://getbootstrap.com/docs/5.3/getting-started/introduction/) and some custom CSS
- [Cropper.js](https://symfony.com/bundles/ux-cropperjs/current/index.html) for cropping images during uploads
- [LiipImagine Bundle](https://symfony.com/bundles/LiipImagineBundle/current/index.html) for handling image formats

### And various quality tools:
- [GitHub Actions](https://docs.github.com/en/actions) for CI
- [PHPStan](https://phpstan.org/) at level 9 to check code consistency
- [CS Fixer](https://github.com/PHP-CS-Fixer/PHP-CS-Fixer) to apply best practices and automatically fix issues
- [PHPUnit](https://phpunit.de/index.html) for unit and functional testing
- [GrumPHP](https://github.com/phpro/grumphp) to check commit format and run some CI checks before pushing

## Good to know
- The *.env* file contains only development environment variables. The production variables need to be defined in a *.env.local* file, and you must run the command `composer dump-env prod` on the production server.
- If you have any doubts about the production server environment or if you need a list of required dependencies, you can check the *Dockerfile*.
- The *docs* folder at the root of the project contains the documentation for the FrankenPHP version used in this project.
- The Command classes are unnecessary for this project but have been retained as documentation.
- You can check the *.github/workflows/ci.yml* file to see the different instructions that are run, if you want to execute them manually.
- Each controller file contains only one route and method to adhere to the single responsibility principle.