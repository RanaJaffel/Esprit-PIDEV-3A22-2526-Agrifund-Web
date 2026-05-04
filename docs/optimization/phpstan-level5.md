# PHPStan Level 5 Report

Generated from:

```bash
vendor/bin/phpstan analyse --configuration=phpstan.level5.neon --no-progress --error-format=json
```

## Current Result

- File errors: 200
- General errors: 0
- Status: staged debt; `composer phpstan:5` is expected to fail until these are fixed.

## Top Error Identifiers

| Count | Identifier |
| ---: | --- |
| 132 | `method.notFound` |
| 37 | `argument.type` |
| 7 | `method.unused` |
| 6 | `nullCoalesce.expr` |
| 4 | `function.alreadyNarrowedType` |
| 4 | `arguments.count` |
| 3 | `property.onlyWritten` |
| 3 | `notIdentical.alwaysTrue` |
| 2 | `nullCoalesce.offset` |
| 1 | `match.alwaysTrue` |

## Hottest Files

| Count | File |
| ---: | --- |
| 20 | `src/Controller/Banque/BanqueMessagerieController.php` |
| 20 | `src/Controller/Admin/AdminMessagerieController.php` |
| 15 | `src/Controller/Agriculteur/AgriculteurMessagerieController.php` |
| 14 | `src/Controller/Agriculteur/AgriculteurProfileController.php` |
| 12 | `src/Command/CreateUsersCommand.php` |
| 11 | `src/Controller/Banque/BanqueProfileController.php` |
| 9 | `src/Controller/Admin/AdminProfileController.php` |
| 9 | `src/Controller/FaceRecognitionController.php` |
| 8 | `src/Controller/Security/LoginController.php` |
| 8 | `src/EventListener/LoginListener.php` |

## Cleanup Order

1. Fix missing methods by tightening user/entity types after `getUser()` and repository calls.
2. Fix argument type mismatches in controllers and services.
3. Remove genuinely unused private methods and unreachable/null-coalescing branches.
4. Re-run `composer phpstan:5` after each feature area is cleaned.
