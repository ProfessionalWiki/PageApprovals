# Page Approvals

[![GitHub Workflow Status](https://img.shields.io/github/actions/workflow/status/ProfessionalWiki/PageApprovals/ci.yml?branch=master)](https://github.com/ProfessionalWiki/PageApprovals/actions?query=workflow%3ACI)
[![Type Coverage](https://shepherd.dev/github/ProfessionalWiki/PageApprovals/coverage.svg)](https://shepherd.dev/github/ProfessionalWiki/PageApprovals)
[![Psalm level](https://shepherd.dev/github/ProfessionalWiki/PageApprovals/level.svg)](psalm.xml)
[![Latest Stable Version](https://poser.pugx.org/professional-wiki/PageApprovals/version.png)](https://packagist.org/packages/professional-wiki/page-approvals)
[![Download count](https://poser.pugx.org/professional-wiki/PageApprovals/d/total.png)](https://packagist.org/packages/professional-wiki/page-approvals)

MediaWiki extension for approving pages.

[Professional.Wiki], the creator of this extension, provides
[MediaWiki Development], [MediaWiki Hosting], and [MediaWiki Consulting].

**Table of Contents**

- [Usage](#usage-documentation)
- [Installation](#installation)
- [PHP Configuration](#php-configuration)
- [Development](#development)
- [Release notes](#release-notes)

## Usage Documentation

TODO

## Installation

Platform requirements:

* [PHP] 8.1 or later (tested up to 8.3)
* [MediaWiki] 1.39 or later (tested up to 1.43-dev)

The recommended way to install the Page Approvals extension is with [Composer] and
[MediaWiki's built-in support for Composer][Composer install].

On the commandline, go to your wikis root directory. Then run these two commands:

```shell script
COMPOSER=composer.local.json composer require --no-update professional-wiki/page-approvals:~1.0
```

```shell script
composer update professional-wiki/page-approvals --no-dev -o
```

Then enable the extension by adding the following to the bottom of your wikis [LocalSettings.php] file:

```php
wfLoadExtension( 'PageApprovals' );
```

You can verify the extension was enabled successfully by opening your wikis Special:Version page.

## PHP Configuration

Configuration can be changed via [LocalSettings.php].

TODO

## Development

Run `composer install` in `extensions/PageApprovals/` to make the code quality tools available.

### Running Tests and CI Checks

You can use the `Makefile` by running make commands in the `PageApprovals` directory.

* `make ci`: Run everything
* `make test`: Run all tests
* `make phpunit --filter FooBar`: run only PHPUnit tests with FooBar in their name
* `make phpcs`: Run all style checks
* `make cs`: Run all style checks and static analysis

### Updating Baseline Files

Sometimes Psalm and PHPStan generate errors or warnings we do not wish to fix.
These can be ignored by adding them to the respective baseline file. You can update
these files with `make stan-baseline` and `make psalm-baseline`.

### Inserting Test Data

```sql
INSERT INTO approver_config (ac_user_id, ac_categories)
VALUES (1, 'TestCat|TestCat2');
```

## Release Notes

### Version 1.0.0 - 2024-0x-xx

* Approval UI on regular wiki pages that shows the approval status and allows approvers to change said status
* API endpoints to approve and unapprove pages
* Automatic unapproval of pages when their displayed content changes
* Detection of changes to displayed content via embedded constructs such as templates or SMW queries
* Approver management page and associated MediaWiki right
* Compatibility with MediaWiki 1.39 up to 1.43-dev
* Compatibility with PHP 8.1 up to 8.3

[Professional.Wiki]: https://professional.wiki
[MediaWiki Hosting]: https://pro.wiki
[MediaWiki Development]: https://professional.wiki/en/mediawiki-development
[MediaWiki Consulting]: https://professional.wiki/en/mediawiki-consulting-services
[MediaWiki]: https://www.mediawiki.org
[PHP]: https://www.php.net
[Composer]: https://getcomposer.org
[Composer install]: https://professional.wiki/en/articles/installing-mediawiki-extensions-with-composer
[LocalSettings.php]: https://www.pro.wiki/help/mediawiki-localsettings-php-guide
