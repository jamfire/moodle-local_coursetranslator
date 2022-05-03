# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [0.9.4] - 2022-05-3

### Added
- Edit links per field
- View source button on translate page to inspect mlang tags in content

### Fixed
- Edit links that were broken in amd module
- Allow users to edit content in different languages at same time

### Changed
- Using SonarCloud automatic analysis instead of github action

## [0.9.3] - 2022-05-02

### Added
- CI/CD workflow for Moodle Plugin CI, SonarCloud, and Dependency Review

## [0.9.2] - 2022-05-01

### Added
- Status and status visibility controls
- local_coursetranslator db table for managing status of updates
- Detect when content contains multiple mlang tags of the same language

### Changed
- Documentation to mustache template
- Removed ID in favor of status on translation grid

## Fixed
- Code prechecks

## [0.9.1] - 2022-04-30

### Added
- Detect split {mlang} tags and notify editor to use standard moodle form instead.
- Default edit link added to labels on translation page.

### Fixed
- Code prechecks

## [0.9.0] - 2022-04-25

### Added
- Initial release
- Translate content page
- Web Service for ajax database updates
- Custom atto editor for translation page

## Changelog Format

```
## [0.0.0] - YYYY-MM-DD

### Added
- _For new features._

### Changed
- _For changes in existing functionality._

### Deprecated
- _For once-stable features removed in upcoming releases._

### Removed
- _For deprecated features removed in this release._

### Fixed
- _For any bug fixes._

### Security
- _To invite users to upgrade in case of vulnerabilities._
```