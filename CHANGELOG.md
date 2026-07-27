# Changelog

## [2.2.2](https://github.com/WebFiori/mail/compare/v2.2.1...v2.2.2) (2026-07-27)


### Features

* **oauth:** add OAuthTokenProvider interface, MicrosoftOAuthProvider, SESCredentialHelper ([8282dc3](https://github.com/WebFiori/mail/commit/8282dc33a2958b9a30644767f17480d353034af4))


### Bug Fixes

* **headers:** generate Message-ID header and wire In-Reply-To ([61f2734](https://github.com/WebFiori/mail/commit/61f2734c7b3936e51fe77ae4ebf5bbd8184b5fa6)), closes [#48](https://github.com/WebFiori/mail/issues/48)
* **security:** enable SSL/TLS peer verification by default ([b6577d9](https://github.com/WebFiori/mail/commit/b6577d9ec4ec9196f71afb40a4bd89e7c30947ca)), closes [#45](https://github.com/WebFiori/mail/issues/45)
* **timeout:** apply stream timeout after connect and retry with backoff ([995b666](https://github.com/WebFiori/mail/commit/995b6663d36fff98f6380fbbae7a1ca6614d2016)), closes [#50](https://github.com/WebFiori/mail/issues/50)
* **validation:** validate email addresses in addTo/addCC/addBCC ([f471fca](https://github.com/WebFiori/mail/commit/f471fcaafce6c8bd48b60ddf2f26e676c64ea129)), closes [#46](https://github.com/WebFiori/mail/issues/46)


### Miscellaneous Chores

* Merge pull request [#73](https://github.com/WebFiori/mail/issues/73) from WebFiori/dev ([aaf454a](https://github.com/WebFiori/mail/commit/aaf454a51316a1cb82be7ebe7f7b48e871467cc2))

## [2.2.1](https://github.com/WebFiori/mail/compare/v2.2.0...v2.2.1) (2026-06-15)


### Bug Fixes

* **smtp:** use base64 encoding for email body parts ([18185e8](https://github.com/WebFiori/mail/commit/18185e83a3f907197dbc802505ca0a55cc2cd2fb))
* **smtp:** use base64 encoding for email body parts ([cb17c1c](https://github.com/WebFiori/mail/commit/cb17c1cedab9a6eabeeca76e3186c18d185c59d9))

## [2.2.0](https://github.com/WebFiori/mail/compare/v2.1.1...v2.2.0) (2026-06-13)


### Features

* Add SMTP enhancements for connection reuse, multipart/alternative, and greylisting retry ([ebb9ec2](https://github.com/WebFiori/mail/commit/ebb9ec2ed2f1ba72346c2424f0b4339ca349333b))
* Add TransportInterface and SmtpTransport ([b8fdc83](https://github.com/WebFiori/mail/commit/b8fdc832cd659f4a79feda5a3cd3c320191a0afc))
* Add TransportInterface and SmtpTransport ([a53c1e9](https://github.com/WebFiori/mail/commit/a53c1e9bdb66516ba39550509bdd8f2be9872f5b))
* SMTP enhancements for connection reuse, multipart/alternative, and greylisting retry ([d6460f2](https://github.com/WebFiori/mail/commit/d6460f20b73a755a1a33cdea88cc71ab13f5f77b))
* v2.2.0 — Transport abstraction, SMTP enhancements, and README overhaul ([8f398d6](https://github.com/WebFiori/mail/commit/8f398d64ae23d1ce597884d19aef27eb1c2ec6e4))


### Miscellaneous Chores

* Add license header to new files ([4216b23](https://github.com/WebFiori/mail/commit/4216b23f660d75933603adbfc4b8192777cf3cb7))
* Correcting License Headers ([ae3d154](https://github.com/WebFiori/mail/commit/ae3d15402075e78f4d2b69cc068093cd973724a4))
* File Bump-up ([03faed1](https://github.com/WebFiori/mail/commit/03faed1a7bd5b4202a52880200de933a82c3affc))

## [2.1.1](https://github.com/WebFiori/mail/compare/v2.1.0...v2.1.1) (2026-06-02)


### Miscellaneous Chores

* Verstion Update ([950060d](https://github.com/WebFiori/mail/commit/950060d10399d1480cea4fa90d794096723ce088))

## [2.1.0](https://github.com/WebFiori/mail/compare/v2.0.0...v2.1.0) (2025-10-07)


### Miscellaneous Chores

* Merge pull request [#39](https://github.com/WebFiori/mail/issues/39) from WebFiori/dev ([0fbe9f1](https://github.com/WebFiori/mail/commit/0fbe9f1998b71f2df456cb2d11f7a623f353be2f))

## [2.0.0](https://github.com/WebFiori/mail/compare/v1.3.1...v2.0.0) (2025-09-09)


### Features

* Added Support for OAuth Authentication ([d06cf9a](https://github.com/WebFiori/mail/commit/d06cf9a3d6ac21cde918a72419add03bdf08a6a7))


### Miscellaneous Chores

* Fix CS Config ([db2275e](https://github.com/WebFiori/mail/commit/db2275e80abff935c16dd2457d0f3d6498ebf3f3))
* Run CS Fixer ([5e6beed](https://github.com/WebFiori/mail/commit/5e6beed7b27ab6fdba92a413b1e50c93833d3567))
* Updated Dependencies ([113bd52](https://github.com/WebFiori/mail/commit/113bd52ce670b1b1241c852de12050da39629aef))


### Documentation

* Enhanced README + Added Usage Examples ([e299238](https://github.com/WebFiori/mail/commit/e2992388882c3ddec4173287ffd3ec35fac56235))

## [1.3.1](https://github.com/WebFiori/mail/compare/v1.3.0...v1.3.1) (2024-12-24)


### Bug Fixes

* Position of Notice ([4fe92a1](https://github.com/WebFiori/mail/commit/4fe92a1cfcaec1adc7d1003ea4c116188603cb04)), closes [#31](https://github.com/WebFiori/mail/issues/31)


### Miscellaneous Chores

* Update .gitattributes ([1cf4a0f](https://github.com/WebFiori/mail/commit/1cf4a0f30a2cc6364c540b1c95a3d55f7afb79ce))

## [1.3.0](https://github.com/WebFiori/mail/compare/v1.2.2...v1.3.0) (2024-12-24)


### Features

* Added Ability to Have More Than One Testing Address ([58eaa69](https://github.com/WebFiori/mail/commit/58eaa69d66b3a7868da3403e9f8ff5f0f813cc0c))
* Improved Testing Mode ([069a4c9](https://github.com/WebFiori/mail/commit/069a4c99509a0eb40ec1f6ac1c737a4e4387cd18))


### Bug Fixes

* Added Check if Message was Already Sent ([d189ddc](https://github.com/WebFiori/mail/commit/d189ddc71a3f8273fcd8e4843f7dd21501d42707))
* Fix Issue With Sample ([ad9f6f2](https://github.com/WebFiori/mail/commit/ad9f6f291cca638139d1917061af04451ed23529))


### Miscellaneous Chores

* Added CS Fixer Cache to .gitignore ([427d117](https://github.com/WebFiori/mail/commit/427d1173918984e9b97c5da21dd2dc10ab6bd149))
* Added Release Please ([3374286](https://github.com/WebFiori/mail/commit/337428682a8ca923c15d2f271cec0871da3e54b0))
* **main:** release 1.2.1 ([c45b3e2](https://github.com/WebFiori/mail/commit/c45b3e28aa439484a5d5ea81174fdba44453c202))
* **main:** release 1.2.2 ([16b3cf0](https://github.com/WebFiori/mail/commit/16b3cf0d31ee3b1f22d64765b764f0034bbe4557))
* Run CS Fixer ([e06aecd](https://github.com/WebFiori/mail/commit/e06aecddd2f57ea0c496792b082096dcdadb9f20))
* Updated GitAttributes ([70660bc](https://github.com/WebFiori/mail/commit/70660bc0985d2169c8b9b0b172204bb50cef91c7))
* Updated README by Adding PHP 8.4 ([b6dc429](https://github.com/WebFiori/mail/commit/b6dc429c56a8da3b81d46814a6cf3b7de07c8789))
* Updated Release Please Config ([7695559](https://github.com/WebFiori/mail/commit/7695559beda52cdb8ac95ccab50e26b1f2b315bc))

## [1.2.2](https://github.com/WebFiori/mail/compare/v1.2.1...v1.2.2) (2024-12-24)


### Miscellaneous Chores

* Updated GitAttributes ([70660bc](https://github.com/WebFiori/mail/commit/70660bc0985d2169c8b9b0b172204bb50cef91c7))
* Updated Release Please Config ([7695559](https://github.com/WebFiori/mail/commit/7695559beda52cdb8ac95ccab50e26b1f2b315bc))

## [1.2.1](https://github.com/WebFiori/mail/compare/v1.2.0...v1.2.1) (2024-12-03)


### Bug Fixes

* Added Check if Message was Already Sent ([d189ddc](https://github.com/WebFiori/mail/commit/d189ddc71a3f8273fcd8e4843f7dd21501d42707))


### Miscellaneous Chores

* Added Release Please ([3374286](https://github.com/WebFiori/mail/commit/337428682a8ca923c15d2f271cec0871da3e54b0))
* Updated README by Adding PHP 8.4 ([b6dc429](https://github.com/WebFiori/mail/commit/b6dc429c56a8da3b81d46814a6cf3b7de07c8789))
