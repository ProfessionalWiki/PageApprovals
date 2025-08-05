# Page Approvals

[![GitHub Workflow Status](https://img.shields.io/github/actions/workflow/status/ProfessionalWiki/PageApprovals/ci.yml?branch=master)](https://github.com/ProfessionalWiki/PageApprovals/actions?query=workflow%3ACI)
[![Type Coverage](https://shepherd.dev/github/ProfessionalWiki/PageApprovals/coverage.svg)](https://shepherd.dev/github/ProfessionalWiki/PageApprovals)
[![Psalm level](https://shepherd.dev/github/ProfessionalWiki/PageApprovals/level.svg)](psalm.xml)
[![Latest Stable Version](https://poser.pugx.org/professional-wiki/page-approvals/v/stable)](https://packagist.org/packages/professional-wiki/page-approvals)
[![Download count](https://poser.pugx.org/professional-wiki/page-approvals/downloads)](https://packagist.org/packages/professional-wiki/page-approvals)

Quality control for your wiki. Mark pages as approved or request review from approvers.
Read more in the [Page Approvals documentation](https://professional.wiki/en/extension/page-approvals).

**Table of Contents**

- [Usage](#usage-documentation)
- [Installation](#installation)
- [PHP Configuration](#php-configuration)
- [Development](#development)
- [Release notes](#release-notes)


[Professional Wiki] created this extension and provides
[MediaWiki Development], [MediaWiki Hosting], and [MediaWiki Consulting] services.

## Usage Documentation

See the [Page Approvals usage documentation](https://professional.wiki/en/extension/page-approvals#Usage).

[![Image](https://github.com/user-attachments/assets/7aaf8615-8eaa-4f53-a125-ef02423f4625)](https://professional.wiki/en/extension/page-approvals)

## Installation

Platform requirements:

* [PHP] 8.1 or later (tested up to 8.3)
* [MediaWiki] 1.39 or later (tested up to 1.43)

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

Run the [update script](https://www.mediawiki.org/wiki/Special:MyLanguage/Manual:Update.php) which will automatically create the necessary database tables that this extension needs.

You can verify the extension was enabled successfully by opening your wikis Special:Version page.

## PHP Configuration

Configuration can be changed via [LocalSettings.php].

See the [Page Approvals configuration reference](https://professional.wiki/en/extension/page-approvals#Configuration).

## Development

Run `composer install` in `extensions/PageApprovals/` to make the code quality tools available.

### Running Tests and CI Checks

You can use the `Makefile` by running make commands in the `PageApprovals` directory.

* `make ci`: Run everything
* `make test`: Run all tests
* `make phpunit --filter FooBar`: run only PHPUnit tests with FooBar in their name
* `make phpcs`: Run all style checks
* `make cs`: Run all style checks and static analysis
* `make lint-docker`: Run all JavaScript and CSS linting

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

### Version 2.1.0 - 2025-08-05

* Added [Admin Links](https://www.mediawiki.org/wiki/Extension:Admin_Links) integration
* Added ability to add intro text to the `Special:ManageApprovers` page via `MediaWiki:Ext-pageapprovals-manage-intro`

### Version 2.0.0 - 2025-08-01

* Raided the minimum MediaWiki version from 1.39 to 1.43
* Added support for MediaWiki 1.44
* Improved approval UI
    * More integrated look-and-feel for the approval badge and dropdown (by using Codex components)
    * Timestamp moved into the tooltip for cleaner presentation
    * Always show a fully up-to-date timestamp (by generating it via JavaScript)
* Improved handling for the Vector 2022 skin

### Version 1.0.0 - 2024-10-28

* Approval UI on regular wiki pages that shows the approval status and allows approvers to change said status
* API endpoints to approve and unapprove pages
* Automatic unapproval of pages when their displayed content changes
* Detection of changes to displayed content via embedded constructs such as templates or SMW queries
* Approver management page and associated MediaWiki right (Special:ManageApprovers)
* Personalized list of pending approvals (Special:PendingApprovals)
* Compatibility with MediaWiki 1.39 up to 1.43-dev
* Compatibility with PHP 8.1 up to 8.3

[Professional Wiki]: https://professional.wiki
[MediaWiki Hosting]: https://pro.wiki
[MediaWiki Development]: https://professional.wiki/en/mediawiki-development
[MediaWiki Consulting]: https://professional.wiki/en/mediawiki-consulting-services
[MediaWiki]: https://www.mediawiki.org
[PHP]: https://www.php.net
[Composer]: https://getcomposer.org
[Composer install]: https://professional.wiki/en/articles/installing-mediawiki-extensions-with-composer
[LocalSettings.php]: https://www.pro.wiki/help/mediawiki-localsettings-php-guide
